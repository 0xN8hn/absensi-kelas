<?php 
$host = getenv('MYSQLHOST')     ?: 'mysql.railway.internal';
$user = getenv('MYSQLUSER')     ?: 'root';
$pass = getenv('MYSQLPASSWORD') ?: 'bivJpwmzTAkYQPEGotbjoPcVWXxrVXsf';
$name = getenv('MYSQLDATABASE') ?: 'railway';
$port = getenv('MYSQLPORT')     ?: 3306;

$conn = new mysqli($host, $user, $pass, $name, (int)$port);

if ($conn->connect_error) {
    header('Content-Type: application/json');
    die(json_encode([
        'ok'  => false,
        'msg' => 'Koneksi DB gagal: ' . $conn->connect_error
    ]));
}

$conn->set_charset('utf8mb4');
?>