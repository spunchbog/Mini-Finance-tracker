<?php
// db_connect.php

// Use environment variables (set on Render), with local XAMPP defaults as fallback
$host    = getenv('DB_HOST') ?: 'localhost';
$port    = getenv('DB_PORT') ?: 3306;
$user    = getenv('DB_USER') ?: 'root';
$pass    = getenv('DB_PASS') ?: '';
$dbname  = getenv('DB_NAME') ?: 'fintrack';
$charset = 'utf8mb4';

// Path to CA certificate (only needed for SSL connections like Aiven)
$ca_path = __DIR__ . '/ca.pem';
$use_ssl = getenv('DB_HOST') ? true : false; // only use SSL when running on Render/Aiven

// ---------- mysqli connection ----------
$conn = mysqli_init();

if ($use_ssl && file_exists($ca_path)) {
    mysqli_ssl_set($conn, NULL, NULL, $ca_path, NULL, NULL);
    mysqli_real_connect($conn, $host, $user, $pass, $dbname, $port, NULL, MYSQLI_CLIENT_SSL);
} else {
    mysqli_real_connect($conn, $host, $user, $pass, $dbname, $port);
}

if (mysqli_connect_errno()) {
    die("Connection failed: " . mysqli_connect_error());
}

// ---------- PDO connection ----------
$dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

if ($use_ssl && file_exists($ca_path)) {
    $options[PDO::MYSQL_ATTR_SSL_CA] = $ca_path;
}

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}
?>