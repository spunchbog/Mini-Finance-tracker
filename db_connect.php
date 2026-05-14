<?php
// db_connect.php
$host = "10.163.1.185";
$user = "fintracker";
$pass = "";
$dbname = "fintrack";

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>