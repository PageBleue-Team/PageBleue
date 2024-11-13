<?php

namespace App\Exception;

class DatabaseException extends \RuntimeException
{
    /**
     * Créer une nouvelle instance d'exception de base de données.
     *
     * @param string $message
     * @param int $code
     * @param \Throwable|null $previous
     */
    public function __construct($message = "", $code = 0, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    /**
     * Créer une nouvelle instance d'exception de base de données à partir d'une exception PDOException.
     *
     * @param \PDOException $pdoException
     * @return DatabaseException
     */
    public static function fromPDOException(\PDOException $pdoException): DatabaseException
    {
        return new self($pdoException->getMessage(), $pdoException->getCode(), $pdoException);
    }
}
