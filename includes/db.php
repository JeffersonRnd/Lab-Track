<?php
require_once __DIR__ . '/../config/config.php';

function db(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
        try {
            $pdo = new PDO(
                'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]
            );
            // Alinear la zona horaria de MySQL con la de PHP
            $stmt = $pdo->prepare('SET time_zone = ?');
            $stmt ->execute([date('P')]);

            
        } catch (PDOException $e) {
            http_response_code(500);
            die('<h1>Error de conexion</h1><p>No se pudo conectar a la base de datos. '
                . 'Verifique que MySQL este activo en XAMPP y que haya importado database/labtrack.sql.</p>');
        }
    }
    return $pdo;
}
