<?php

namespace Config;

class Cache
{
    private string $cacheDir;
    private int $defaultTtl;

    public function __construct(string $cacheDir, int $defaultTtl = 3600)
    {
        $this->cacheDir = $cacheDir;
        $this->defaultTtl = $defaultTtl;
        $this->initCacheDir();
    }

    private function initCacheDir(): void
    {
        if (!is_dir($this->cacheDir)) {
            if (!mkdir($this->cacheDir, 0755, true)) {
                throw new \RuntimeException("Une erreur est survenue lors de la création du dossier Cache");
            }
        }
    }

    public function remember(string $key, callable $callback, ?int $ttl = null): mixed
    {
        $ttl = $ttl ?? $this->defaultTtl;
        $cacheFile = $this->getCacheFilePath($key);
        if ($this->isValid($cacheFile, $ttl)) {
            return $this->get($cacheFile);
        }

        $data = $callback();
        $this->put($cacheFile, $data);
        return $data;
    }

    private function isValid(string $cacheFile, int $ttl): bool
    {
        return file_exists($cacheFile) && (time() - filemtime($cacheFile) < $ttl);
    }

    private function getCacheFilePath(string $key): string
    {
        return $this->cacheDir . '/' . md5($key) . '.cache';
    }

    private function get(string $cacheFile): mixed
    {
        $content = file_get_contents($cacheFile);
        if ($content === false) {
            throw new \RuntimeException("Impossible de lire le fichier cache: $cacheFile");
        }
        return unserialize($content);
    }

    private function put(string $cacheFile, mixed $data): void
    {
        file_put_contents($cacheFile, serialize($data));
    }

    public function forget(string $key): bool
    {
        $cacheFile = $this->getCacheFilePath($key);
        return file_exists($cacheFile) ? unlink($cacheFile) : false;
    }

    public function clear(): void
    {
        $files = glob($this->cacheDir . '/*.cache');
        if ($files === false) {
            throw new \RuntimeException("Impossible de lister les fichiers cache");
        }
        array_map('unlink', $files);
    }
}
