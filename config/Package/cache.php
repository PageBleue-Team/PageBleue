<?php

namespace Config;

class Cache
{
    private string $cacheDir;
    private int $defaultTtl;

    public function __construct(string $cacheDir, int $defaultTtl = 3600)
    {
        if ($defaultTtl <= 0) {
            throw new \RuntimeException("Le TTL doit être positif");
        }
        $cacheDir = realpath($cacheDir) ?: $cacheDir;
        if (empty($cacheDir) || strpos($cacheDir, '..') !== false) {
            throw new \RuntimeException("Chemin du cache invalide");
        }
        $this->cacheDir = $cacheDir;
        $this->defaultTtl = $defaultTtl;
        $this->initCacheDir();
    }

    private function initCacheDir(): void
    {
        if (!is_dir($this->cacheDir)) {
            if (!mkdir($this->cacheDir, 0750, true)) {
                throw new \RuntimeException("Une erreur est survenue lors de la création du dossier Cache");
            }
            if (!chmod($this->cacheDir, 0750)) {
                throw new \RuntimeException("Impossible de définir les permissions du dossier Cache");
            }
        }
    }

    public function remember(string $key, callable $callback, ?int $ttl = null): mixed
    {
        $ttl = $ttl ?? $this->defaultTtl;
        $cacheFile = $this->getCacheFilePath($key);
        $lockFile = $cacheFile . '.lock';
        $lockHandle = fopen($lockFile, 'w+');
        
        if (!$lockHandle || !flock($lockHandle, LOCK_EX)) {
            throw new \RuntimeException("Impossible d'acquérir le verrou");
        }

        try {
            if ($this->isValid($cacheFile, $ttl)) {
                $data = $this->get($cacheFile);
            } else {
                $data = $callback();
                $this->put($cacheFile, $data);
            }
            return $data;
        } catch (\Throwable $e) {
            throw new \RuntimeException(
                "Erreur lors de l'exécution du cache: " . $e->getMessage(),
                0,
                $e
            );
        } finally {
            flock($lockHandle, LOCK_UN);
            fclose($lockHandle);
            @unlink($lockFile);
        }
    }

    private function isValid(string $cacheFile, int $ttl): bool
    {
        return file_exists($cacheFile) && (time() - filemtime($cacheFile) < $ttl);
    }

    private function getCacheFilePath(string $key): string
    {
        return rtrim($this->cacheDir, '/') . '/' . hash('sha256', $key) . '.cache';
    }

    private function get(string $cacheFile): mixed
    {
        if (!is_readable($cacheFile)) {
            throw new \RuntimeException("Le fichier cache n'est pas lisible: $cacheFile");
        }
        $content = file_get_contents($cacheFile);
        if ($content === false) {
            throw new \RuntimeException("Impossible de lire le fichier cache: $cacheFile");
        }
        $data = @unserialize($content);
        if ($data === false && $content !== serialize(false)) {
            throw new \RuntimeException("Données du cache corrompues");
        }
        return $data;
    }

    private function put(string $cacheFile, mixed $data): void
    {
        $tempFile = $cacheFile . '.tmp';
        if (file_put_contents($tempFile, serialize($data), LOCK_EX) === false) {
            throw new \RuntimeException("Échec de l'écriture des données dans le cache");
        }
        if (!rename($tempFile, $cacheFile)) {
            @unlink($tempFile);
            throw new \RuntimeException("Échec de la finalisation du cache");
        }
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
