<?php

namespace Config;

use Symfony\Component\Yaml\Yaml;

class SiteConfig
{
    /** @var array<string, mixed> */
    private static array $config = [];
    private static bool $initialized = false;

    public static function init(): void
    {
        if (self::$initialized) {
            return;
        }

        $isDebug = $_ENV['APP_ENV'] === 'development';
        $yamlDirectory = dirname(__DIR__, 2) . '/public/texts/';
        $yamlFiles = glob($yamlDirectory . '*.yaml');
        if ($yamlFiles === false) {
            throw new \RuntimeException('Impossible de lire les fichiers YAML');
        }

        foreach ($yamlFiles as $yamlPath) {
            $yaml = Yaml::parseFile($yamlPath);
            if ($isDebug) {
                var_dump([
                    'loading_file' => $yamlPath,
                    'content' => $yaml
                ]);
            }
            self::$config = array_merge(self::$config, $yaml);
        }

        self::$initialized = true;

        if ($isDebug) {
            var_dump([
                'final_config' => self::$config,
                'initialized' => self::$initialized
            ]);
        }
    }

    /**
     * @return mixed
     */
    public static function get(string $key, bool $autoFormat = true)
    {
        if (!self::$initialized) {
            self::init();
        }

        $isDebug = $_ENV['APP_ENV'] === 'development';
        $keys = explode('.', $key);
        $value = self::$config;

        if ($isDebug) {
            var_dump([
                'requested_key' => $key,
                'keys_array' => $keys,
                'current_config' => self::$config
            ]);
        }

        foreach ($keys as $k) {
            if (!isset($value[$k])) {
                if ($isDebug) {
                    var_dump([
                        'missing_key' => $k,
                        'available_keys' => array_keys($value)
                    ]);
                }
                throw new \RuntimeException(sprintf(
                    "La clé de configuration '%s' n'existe pas",
                    $key
                ));
            }
            $value = $value[$k];
        }

        if ($autoFormat) {
            return self::formatValue($value);
        }

        return $value;
    }

    /**
     * @param mixed $value
     * @return mixed
     */
    private static function formatValue($value)
    {
        if (is_array($value)) {
            // Si c'est un tableau avec des sous-tableaux contenant 'name'
            if (isset($value[0]) && is_array($value[0]) && isset($value[0]['name'])) {
                return $value;
            }

            // Si c'est un tableau associatif
            if (array_keys($value) !== range(0, count($value) - 1)) {
                return $value;
            }

            // Pour les tableaux simples de chaînes
            $filtered = array_filter($value, 'is_string');
            if (count($filtered) === count($value)) {
                return implode("\n", $value);
            }

            // Pour tout autre type de tableau
            return $value;
        }
        return $value;
    }

    /**
     * @return array<string, mixed>
     */
    public static function getRawConfig(): array
    {
        return self::$config;
    }
}
