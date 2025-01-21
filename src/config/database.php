<?php

//use PDO;

return function () {
    $host = $_ENV['DB_HOST'];
    $port = $_ENV['DB_PORT'];
    $database = $_ENV['DB_DATABASE'];
    $username = $_ENV['DB_USERNAME'];
    $password = $_ENV['DB_PASSWORD'];
    
    $dsn = "mysql:host=$host;port=$port;dbname=$database;charset=utf8mb4";

    return new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
};

// namespace App\Config;

// use PDO;
// use Exception;

// class DatabaseConfig {
//     public static function getPDO() {
//         $dsn = 'mysql:host=' . getenv('DB_HOST') . ';dbname=' . getenv('DB_DATABASE') . ';charset=utf8';
//         $username = getenv('DB_USERNAME');
//         $password = getenv('DB_PASSWORD');

//         try {
//             $pdo = new PDO($dsn, $username, $password, [
//                 PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
//             ]);
//             return $pdo;
//         } catch (PDOException $e) {
//             throw new Exception("Connection failed: " . $e->getMessage());
//         }
//     }
// }

