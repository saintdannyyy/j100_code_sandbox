<?php

/**
 * Environment Configuration Loader
 * Loads variables from .env file into $_ENV and getenv()
 */
class Env
{
    private static $loaded = false;
    private static $variables = [];

    /**
     * Load environment variables from .env file
     * @param string $path Path to .env file
     * @return bool Success status
     */
    public static function load($path = null)
    {
        if (self::$loaded) {
            return true;
        }

        if ($path === null) {
            $path = dirname(__DIR__) . '/.env';
        }

        if (!file_exists($path)) {
            error_log("Environment file not found: $path");
            return false;
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($lines as $line) {
            // Skip comments
            if (strpos(trim($line), '#') === 0) {
                continue;
            }

            // Parse KEY=value
            if (strpos($line, '=') !== false) {
                list($key, $value) = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);

                // Remove quotes if present
                if (preg_match('/^(["\'])(.*)\\1$/', $value, $matches)) {
                    $value = $matches[2];
                }

                // Store in our array
                self::$variables[$key] = $value;

                // Set in environment
                putenv("$key=$value");
                $_ENV[$key] = $value;
            }
        }

        self::$loaded = true;
        return true;
    }

    /**
     * Get an environment variable
     * @param string $key Variable name
     * @param mixed $default Default value if not found
     * @return mixed
     */
    public static function get($key, $default = null)
    {
        // Ensure environment is loaded
        if (!self::$loaded) {
            self::load();
        }

        // Check our cache first
        if (isset(self::$variables[$key])) {
            return self::$variables[$key];
        }

        // Then check $_ENV
        if (isset($_ENV[$key])) {
            return $_ENV[$key];
        }

        // Then try getenv()
        $value = getenv($key);
        if ($value !== false) {
            return $value;
        }

        return $default;
    }

    /**
     * Check if running in production
     * @return bool
     */
    public static function isProduction()
    {
        return self::get('APP_ENV', 'development') === 'production';
    }

    /**
     * Check if debug mode is enabled
     * @return bool
     */
    public static function isDebug()
    {
        return filter_var(self::get('APP_DEBUG', false), FILTER_VALIDATE_BOOLEAN);
    }
}
