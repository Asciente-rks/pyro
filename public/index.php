<?php
declare(strict_types=1);
require_once __DIR__ . '/../src/Database/connection.php';

use Pyro\Database\Connection;

header('Content-Type: text/plain; charset=utf-8');
try {
    Connection::make()->query('SELECT 1');
    echo "OK pyro.local → MySQL connected\n";
} catch (Throwable $e) {
    http_response_code(500);
    echo $e->getMessage() . "\n";
}