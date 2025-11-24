<?php
session_start();
require_once __DIR__ . '/config.php';

header('Content-Type: application/json; charset=utf-8');

function respond($ok, $msg) {
    echo json_encode(['success' => $ok, 'message' => $msg]);
    exit;
}

$sessionKeys = ['usuario_id','user_id','id','usuario'];
$usuario_id = null;
foreach ($sessionKeys as $k) {
    if (!empty($_SESSION[$k])) {
        $usuario_id = (int) $_SESSION[$k];
        break;
    }
}
if (!$usuario_id) {
    respond(false, 'Acesso negado.'); 
}

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

if ($id <= 0) {
    respond(false, 'ID inválido.');
}

$stmt = $conn->prepare("SELECT usuario_id, imagem FROM anuncios WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $id);
$stmt->execute();
$res = $stmt->get_result();
$anuncio = $res->fetch_assoc();
$stmt->close();

if (!$anuncio) {
    respond(false, 'Anúncio não encontrado.');
}

if ((int)$anuncio['usuario_id'] !== $usuario_id) {
    respond(false, 'Você não é o proprietário deste anúncio.');
}

if (!empty($anuncio['imagem'])) {
    $img = ltrim($anuncio['imagem'], '/');
    $path = __DIR__ . '/../' . $img;
    if (strpos($img, 'uploads/') === 0 && file_exists($path)) {
        @unlink($path);
    }
}

$stmt = $conn->prepare("DELETE FROM anuncios WHERE id = ?");
$stmt->bind_param("i", $id);
$ok = $stmt->execute();
$stmt->close();

if ($ok) {
    respond(true, 'Anúncio excluído com sucesso.');
} else {
    respond(false, 'Erro ao excluir.');
}
