using System.Text.Json;

namespace WanGP_Launcher
{
    public class AppSettings
    {
        public string ApiHost { get; set; } = "127.0.0.1";
        public int ApiPort { get; set; } = 8001;
        public string CondaEnvironment { get; set; } = "wan2gp";
        public string CondaPath { get; set; } = "";
        public string ApiUsername { get; set; } = "";
        public string ApiPassword { get; set; } = "";
        public string ServerDirectory { get; set; } = "";
    }

    public static class ConfigManager
    {
        private static readonly string ConfigFileName = "config.json";
        private static string ConfigPath => Path.Combine(AppDomain.CurrentDomain.BaseDirectory, ConfigFileName);

        private static readonly JsonSerializerOptions JsonOptions = new()
        {
            WriteIndented = true
        };

        public static AppSettings Load()
        {
            if (!File.Exists(ConfigPath))
            {
                var defaultSettings = new AppSettings();
                Save(defaultSettings);
                return defaultSettings;
            }

            try
            {
                string json = File.ReadAllText(ConfigPath);
                return JsonSerializer.Deserialize<AppSettings>(json, JsonOptions) ?? new AppSettings();
            }
            catch
            {
                return new AppSettings();
            }
        }

        public static void Save(AppSettings settings)
        {
            try
            {
                string json = JsonSerializer.Serialize(settings, JsonOptions);
                File.WriteAllText(ConfigPath, json);
            }
            catch (Exception ex)
            {
                MessageBox.Show($"Failed to save config: {ex.Message}", "Error", MessageBoxButtons.OK, MessageBoxIcon.Error);
            }
        }
    }
}
