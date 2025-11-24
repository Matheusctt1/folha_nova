<?php
header('Content-Type: application/json');
include 'config.php';

$sql = "SELECT id, titulo, autor, descricao, preco, localidade, imagem, data_criacao FROM anuncios ORDER BY id DESC LIMIT 3;";

$result = $conn->query($sql);

$anuncios = [];
if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {

        $anuncios[] = [
            'id'         => (int)$row['id'],
            'titulo'     => $row['titulo'],
            'autor'      => $row['autor'],
            'preco'      => $row['preco'],
            'localidade' => $row['localidade'],
            'imagem'     => $row['imagem'],
            'data_criacao' => $row['data_criacao'] ?? null
        ];
    }
}

$conn->close();

echo json_encode($anuncios, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
exit;
?>
