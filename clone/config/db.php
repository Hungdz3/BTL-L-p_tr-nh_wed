<?php
// config/db.php

define('DB_HOST', '26.249.30.110');
define('DB_PORT', '3330');
define('DB_NAME', 'dkyhocphan');
define('DB_USER', 'dung');
define('DB_PASS', '123456');
function getDB(): PDO {
    static $pdo = null;

    if ($pdo === null) {
        $dsn = sprintf(
            'pgsql:host=%s;port=%s;dbname=%s',
            DB_HOST, DB_PORT, DB_NAME
        );
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (PDOException $e) {
            http_response_code(500);
            die(json_encode([
                'success' => false,
                'message' => 'Không thể kết nối CSDL PostgreSQL: ' . $e->getMessage()
            ], JSON_UNESCAPED_UNICODE));
        }
    }

    return $pdo;
}
