<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/config.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($id <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID inválido']);
    exit;
}

$sql = "SELECT 
            a.id,
            a.titulo,
            a.autor,
            a.descricao,
            a.preco,
            a.localidade,
            a.imagem,
            a.usuario_id,
            a.categoria,
            a.estado_conservacao,
            u.nome AS vendedor,
            u.email AS vendedor_email,
            u.telefone AS vendedor_telefone
        FROM anuncios a
        LEFT JOIN usuarios u ON a.usuario_id = u.id
        WHERE a.id = ? LIMIT 1";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro no servidor: ' . $conn->error]);
    exit;
}

$stmt->bind_param('i', $id);
$stmt->execute();
$res = $stmt->get_result();
$row = $res->fetch_assoc();
$stmt->close();

if (!$row) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Anúncio não encontrado']);
    exit;
}

$img = '../assets/img/placeholder-book.svg';

if (!empty($row['imagem'])) {

    if (preg_match('#^https?://#i', $row['imagem'])) {
        $img = $row['imagem'];

    } else {
        $candidate = dirname(__DIR__) . '/uploads/' . $row['imagem'];
        if (file_exists($candidate)) {
            $img = '../uploads/' . $row['imagem'];
        }
    }
}

$row['img_path'] = $img;

$row['preco_text'] =
    ($row['preco'] === null || $row['preco'] === '' || $row['preco'] == 0)
        ? 'A combinar'
        : 'R$ ' . number_format((float)$row['preco'], 2, ',', '.');

echo json_encode(['success' => true, 'data' => $row]);
exit;
