<?php

if (
    $_SERVER['HTTP_HOST'] === 'localhost' ||
    strpos($_SERVER['HTTP_HOST'], '127.0.0.1') !== false
) {
    // AMBIENTE LOCAL (XAMPP)
    $host = "localhost";
    $user = "root";
    $pass = "";
    $db   = "XXXX";

} else {
    // AMBIENTE DE PRODUÇÃO (InfinityFree)
    $host = "XXXX";
    $user = "XXXX";
    $pass = "XXXX";
    $db   = "XXXX";
}

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_errno) {
    die("Erro na conexão: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");
?>

