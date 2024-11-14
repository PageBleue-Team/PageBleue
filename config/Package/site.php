<?php

namespace Config;

use Symfony\Component\Yaml\Yaml;

class SiteConfig
{
    private const EXPECTED_YAML_FILES = 5;

    /** @var array */
    private static array $config = [];

    public static function init(): void
    {
        $yamlDirectory = dirname(__DIR__, 2) . '/public/texts/';
        $yamlFiles = glob($yamlDirectory . '*.yaml');

        if (count($yamlFiles) < self::EXPECTED_YAML_FILES) {
            throw new \RuntimeException(sprintf(
                "Nombre insuffisant de fichiers YAML. Minimum attendu: %d, Trouvé: %d",
                self::EXPECTED_YAML_FILES,
                count($yamlFiles)
            ));
        }

        foreach ($yamlFiles as $yamlPath) {
            $yaml = Yaml::parseFile($yamlPath);
            $filename = pathinfo($yamlPath, PATHINFO_FILENAME);
            self::$config[$filename] = $yaml;
        }
    }

    public static function get(string $key)
    {
        if (str_contains($key, '.')) {
            [$file, $entry] = explode('.', $key);

            if (!isset(self::$config[$file])) {
                return null;
            }

            foreach (self::$config[$file] as $data) {
                if (is_array($data) && isset($data[$entry])) {
                    return $data[$entry];
                }
            }
        }
        return self::$config[$key] ?? null;
    }
}
