<?php
declare(strict_types=1);

namespace Pyro\Database;

use PDO;
use PDOException;

final class Connection
{
    public static function make(): PDO
    {
        $root = dirname(__DIR__, 2);
        $envFile = $root . '/.env';
        if (!is_file($envFile)) {
            throw new \RuntimeException("FAIL .env missing at $envFile");
        }

        $env = parse_ini_file($envFile);
        if ($env === false) {
            throw new \RuntimeException('FAIL .env parse error — quote values with ! #');
        }

        $host = $env['DB_HOST'] ?? null;
        $db   = $env['DB_NAME'] ?? null;
        $user = $env['DB_USER'] ?? null;
        $pass = $env['DB_PASSWORD'] ?? $env['DB_PASS'] ?? null;

        foreach (['DB_HOST' => $host, 'DB_NAME' => $db, 'DB_USER' => $user] as $k => $v) {
        if ($v === null || $v === '') {
            throw new \RuntimeException("FAIL .env key $k is missing/empty");
            }
        }
        if (!extension_loaded('pdo_mysql')) {
            throw new \RuntimeException('FAIL pdo_mysql not loaded');
        }

        $dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";
        try {
            $pdo = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            ]);
            $pdo->query('SELECT 1');
            return $pdo;
        } catch (PDOException $e) {
            $driverCode = $e->errorInfo[1] ?? null;
            $hint = match ($driverCode) {
                1045 => "Access denied for '$user'@'$host' — wrong user/password or no rights on '$db'.",
                2002 => "Cannot reach MySQL at '$host' — is wampmysqld64 Running?",
                1049 => "Unknown database '$db' — create it in phpMyAdmin first.",
                default => 'SQLSTATE ' . $e->getCode() . ' — ' . $e->getMessage(),
            };
            throw new \RuntimeException("FAIL PDO [$driverCode] $hint", 0, $e);
        }
    }
}