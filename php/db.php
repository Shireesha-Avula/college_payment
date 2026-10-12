<?php
$host = "sql5.freesqldatabase.com";
$user = "sql5838719";
$password = "95QWlaIDkc";
$database = "sql5838719";
$port = 3306;

$conn = new mysqli($host, $user, $password, $database, $port);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
// echo "Connected successfully!";
?>