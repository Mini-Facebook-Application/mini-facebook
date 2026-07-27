<?php
$host = "localhost";
$dbname = "minifacebook";
$username = "waphuser";
$password = "Waph2026!";

$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed.");
}

$conn->set_charset("utf8mb4");
?>
