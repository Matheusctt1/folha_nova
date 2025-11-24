<?php

if (
    $_SERVER['HTTP_HOST'] === 'localhost' ||
    strpos($_SERVER['HTTP_HOST'], '127.0.0.1') !== false
) {
    // AMBIENTE LOCAL (XAMPP)
    $host = "localhost";
    $user = "root";
    $pass = "";
    $db   = "folhanova";

} else {
    // AMBIENTE DE PRODUÇÃO (InfinityFree)
    $host = "sql302.infinityfree.com";
    $user = "if0_40489043";
    $pass = "090403250703Mj";
    $db   = "if0_40489043_folhanova";
}

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_errno) {
    die("Erro na conexão: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");
?>
