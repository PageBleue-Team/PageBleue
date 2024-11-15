<?php

namespace Config;

class Functions
{
    public static function getCriticalCSS(): string
    {
        $cssPath = ROOT_PATH . '/assets/css/critical.css';
        if (file_exists($cssPath)) {
            $criticalCSS = (string)file_get_contents($cssPath);
            $cleaned = preg_replace('/\s+/', ' ', trim($criticalCSS));
            return $cleaned ?? '/* Error processing CSS */';
        }
        return '/* Critical CSS file not found */';
    }

    public static function safeInclude(string $file): void
    {
        if (file_exists($file)) {
            include_once $file;
        }
    }
}
