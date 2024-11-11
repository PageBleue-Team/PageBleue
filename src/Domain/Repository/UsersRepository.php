<?php

namespace App\Domain\Repository;

use Exception;
use App\Domain\Entity\EntityRepository;
use Config\Database;

class UsersRepository extends EntityRepository
{
    private const MAX_ATTEMPTS = 5;
    private const LOCKOUT_TIME = 900; // 15 minutes en secondes

    public function __construct()
    {
        parent::__construct(Database::getInstance()->getConnection());
    }

    /**
     * @return array{id: int, username: string, password: string, login_attempts: int, last_attempt_time: string}|null
     */
    public function findByUsername(string $username): ?array
    {
        $stmt = $this->pdo->prepare("SELECT id, username, password, login_attempts, last_attempt_time FROM Users WHERE username = :username");
        $stmt->execute(['username' => $username]);
        return $stmt->fetch() ?: null;
    }

    public function resetLoginAttempts(int $userId): void
    {
        $stmt = $this->pdo->prepare("UPDATE Users SET login_attempts = 0 WHERE id = :id");
        $stmt->execute(['id' => $userId]);
    }

    public function incrementLoginAttempts(int $userId): void
    {
        $stmt = $this->pdo->prepare("UPDATE Users SET login_attempts = login_attempts + 1, last_attempt_time = NOW() WHERE id = :id");
        $stmt->execute(['id' => $userId]);
    }

    /**
     * @param array{login_attempts: int, last_attempt_time: string} $user
     */
    public function isAccountLocked(array $user): bool
    {
        return $user['login_attempts'] >= self::MAX_ATTEMPTS &&
               (time() - strtotime($user['last_attempt_time']) < self::LOCKOUT_TIME);
    }

    /**
     * @param array{id: int, username: string, password: string, login_attempts: int, last_attempt_time: string} $user
     */
    public function verifyPassword(string $password, array $user): bool
    {
        return password_verify($password, $user['password']);
    }
}
