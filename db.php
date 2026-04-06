<?php
$host = getenv('MYSQLHOST')     ?: 'localhost';
$user = getenv('MYSQLUSER')     ?: 'root';
$pass = getenv('MYSQLPASSWORD') ?: '';
$name = getenv('MYSQLDATABASE') ?: 'absensi_kelas';
$port = getenv('MYSQLPORT')     ?: 3306;

$conn = new mysqli($host, $user, $pass, $name, $port);
$conn->set_charset('utf8mb4');
?>