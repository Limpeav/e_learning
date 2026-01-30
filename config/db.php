<?php
$host = 'localhost';
$dbname = 'e_learning';
$username = 'root';
$password = ''; // Default XAMPP password is empty

try {
    // For XAMPP on macOS, we often need to specify the unix_socket
    $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
    if (PHP_OS === 'Darwin') {
        $dsn .= ";unix_socket=/Applications/XAMPP/xamppfiles/var/mysql/mysql.sock";
    }
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>