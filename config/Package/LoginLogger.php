<?php

namespace Config;

class LoginLogger
{
    public static function log(string $username, bool $success): void
    {
        $pdo = Database::getInstance()->getConnection();
        // ... reste du code de log ...
    }
}
