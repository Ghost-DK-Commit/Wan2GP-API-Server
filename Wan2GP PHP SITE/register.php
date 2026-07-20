<?php
require_once 'includes/auth.php';

$error = "";
$success = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = t('security_failed');
    } else {
        $username = $_POST['username'] ?? '';
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $accept = isset($_POST['accept_rules']);
        
        if (!$accept) {
            $error = t('accept_rules_error');
        } else {
            if (register_user($username, $email, $password)) {
                $stmt = $pdo->prepare("UPDATE users SET rules_accepted = 1 WHERE email = ?");
                $stmt->execute([$email]);
                $success = t('register_success') . " <a href='login.php'>" . t('login_here') . "</a>";
            } else {
                $error = t('username_email_exists');
            }
        }
    }
}
$csrf_token = generate_csrf_token();
$siteName = get_site_setting('site_name', 'Suno');
$siteLogo = get_site_setting('site_logo', '');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - <?php echo htmlspecialchars($siteName); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/main.css">
    <?php $stmt = $pdo->query("SELECT setting_value FROM site_settings WHERE setting_key = 'active_skin'"); $sk = $stmt->fetchColumn() ?: 'default'; if (file_exists(__DIR__ . '/skins/' . $sk . '/skin.css')): ?>
    <link rel="stylesheet" href="skins/<?php echo $sk; ?>/skin.css">
    <?php endif; ?>
    <style>
        body { display: flex; align-items: center; justify-content: center; height: 100vh; background: var(--bg-main); }
        .auth-card { background: var(--bg-secondary); padding: 48px; border-radius: 32px; border: 1px solid var(--border-color); width: 100%; max-width: 420px; box-shadow: 0 40px 100px rgba(0,0,0,0.8); }
        .auth-card .auth-logo { display: flex; align-items: center; gap: 10px; margin-bottom: 32px; }
        .auth-card .auth-logo-icon {
            width: 40px; height: 40px; border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 18px; font-weight: 900;
            background: linear-gradient(135deg, var(--accent-primary), var(--accent-secondary));
            color: white; overflow: hidden;
        }
        .auth-card .auth-logo-icon img { width: 100%; height: 100%; object-fit: cover; }
        .auth-card .auth-logo-text { font-size: 24px; font-weight: 900; letter-spacing: -0.5px; }
        .auth-card h1 { margin-bottom: 8px; font-weight: 800; font-size: 28px; }
        .auth-card p { color: var(--text-secondary); margin-bottom: 32px; }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 10px; font-size: 14px; color: var(--text-secondary); }
        input { width: 100%; background: var(--bg-tertiary); border: 1px solid var(--border-color); padding: 14px 18px; border-radius: 16px; color: white; font-size: 16px; transition: all 0.3s; font-family: 'Inter', sans-serif; }
        input:focus { border-color: var(--accent-primary); outline: none; box-shadow: 0 0 0 4px rgba(236, 72, 153, 0.1); }
        .btn-auth { width: 100%; margin-top: 10px; font-size: 16px; padding: 16px; }
        .auth-footer { margin-top: 24px; text-align: center; color: var(--text-secondary); font-size: 14px; }
        .auth-footer a { color: var(--accent-primary); text-decoration: none; font-weight: 600; }
        .error-msg { background: rgba(248, 113, 113, 0.1); color: #f87171; padding: 12px; border-radius: 12px; margin-bottom: 20px; font-size: 14px; text-align: center; border: 1px solid rgba(248, 113, 113, 0.2); }
        .success-msg { background: rgba(74, 222, 128, 0.1); color: #4ade80; padding: 12px; border-radius: 12px; margin-bottom: 20px; font-size: 14px; text-align: center; border: 1px solid rgba(74, 222, 128, 0.2); }
    </style>
</head>
<body>
    <div class="auth-card">
        <div class="auth-logo">
            <div class="auth-logo-icon">
                <?php if ($siteLogo): ?>
                    <img src="<?php echo htmlspecialchars($siteLogo); ?>" alt="Logo">
                <?php else: ?>
                    <i class="fa-solid fa-music"></i>
                <?php endif; ?>
            </div>
            <span class="auth-logo-text"><?php echo htmlspecialchars($siteName); ?></span>
        </div>
        <h1 data-t="create_account"><?php echo t('create_account'); ?></h1>
        <p data-t="register_subtext"><?php echo t('register_subtext'); ?></p>
        <?php if($error): ?> <div class="error-msg"><?php echo $error; ?></div> <?php endif; ?>
        <?php if($success): ?> <div class="success-msg"><?php echo $success; ?></div> <?php endif; ?>
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
            <div class="form-group">
                <label data-t="username"><?php echo t('username'); ?></label>
                <input type="text" name="username" placeholder="johndoe" required>
            </div>
            <div class="form-group">
                <label data-t="email_address"><?php echo t('email_address'); ?></label>
                <input type="email" name="email" placeholder="name@example.com" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" placeholder="••••••••" required>
            </div>
            <div class="form-group" style="display: flex; align-items: flex-start; gap: 10px; margin-top: 24px;">
                <input type="checkbox" name="accept_rules" id="accept_rules" required style="width: 20px; height: 20px; margin: 0; cursor: pointer;">
                <label for="accept_rules" style="margin: 0; cursor: pointer; line-height: 1.4;">
                    <span data-t="i_accept"><?php echo t('i_accept'); ?></span> <a data-t="site_rules_terms" href="#" onclick="showRules()" style="color: var(--accent-primary); text-decoration: none; font-weight: 600;"><?php echo t('site_rules_terms'); ?></a>
                </label>
            </div>
            <button data-t="sign_up" type="submit" class="btn-primary btn-auth"><?php echo t('sign_up'); ?></button>
        </form>

        <?php $rules_text = get_site_setting('site_rules', t('default_rules')); ?>
        <div id="rules-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.9); align-items: center; justify-content: center; z-index: 9999; backdrop-filter: blur(12px); padding: 20px;">
            <div style="background: var(--bg-secondary); padding: 40px; border-radius: 40px; max-width: 600px; width: 100%; border: 1px solid var(--border-color); box-shadow: 0 30px 60px rgba(0,0,0,0.6); display: flex; flex-direction: column; max-height: 80vh;">
                <h2 data-t="site_rules_title" style="font-size: 24px; font-weight: 800; margin-bottom: 20px;"><?php echo t('site_rules_title'); ?></h2>
                <div class="custom-scrollbar" style="background: var(--bg-tertiary); padding: 24px; border-radius: 20px; margin-bottom: 24px; font-size: 15px; line-height: 1.8; color: var(--text-secondary); overflow-y: auto; flex: 1; white-space: pre-wrap; text-align: left;">
                    <?php echo htmlspecialchars($rules_text); ?>
                </div>
                <button data-t="close" class="btn-primary" style="width: 100%; padding: 16px; font-size: 16px; border-radius: 16px;" onclick="hideRules()"><?php echo t('close'); ?></button>
            </div>
        </div>
        <script>
        function showRules() { document.getElementById('rules-modal').style.display = 'flex'; }
        function hideRules() { document.getElementById('rules-modal').style.display = 'none'; }
        </script>
        <div class="auth-footer">
            <span data-t="already_account"><?php echo t('already_account'); ?></span> <a data-t="log_in" href="login.php"><?php echo t('log_in'); ?></a>
        </div>
    </div>
</body>
</html>
