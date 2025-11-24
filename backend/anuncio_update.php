<?php
session_start();

require_once __DIR__ . '/config.php';

$sessionKeys = ['usuario_id','user_id','id','usuario'];
$usuario_id = null;
foreach ($sessionKeys as $k) {
    if (!empty($_SESSION[$k])) {
        $usuario_id = (int) $_SESSION[$k];
        break;
    }
}

if (!$usuario_id) {
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
        echo json_encode(['success' => false, 'message' => 'Acesso negado. Faça login.']);
        exit;
    }
    die('Acesso negado.');
}

function e($v){ return htmlspecialchars(trim((string)$v), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }

$id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
if ($id <= 0) {
    echo json_encode(['success'=>false,'message'=>'ID inválido.']);
    exit;
}

$stmt = $conn->prepare("SELECT usuario_id, imagem FROM anuncios WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $id);
$stmt->execute();
$res = $stmt->get_result();
$anuncio = $res->fetch_assoc();
$stmt->close();

if (!$anuncio) {
    echo json_encode(['success'=>false,'message'=>'Anúncio não encontrado.']);
    exit;
}

if ((int)$anuncio['usuario_id'] !== $usuario_id) {
    echo json_encode(['success'=>false,'message'=>'Você não é o proprietário deste anúncio.']);
    exit;
}

$titulo     = e($_POST['titulo'] ?? '');
$autor      = e($_POST['autor'] ?? '');
$preco      = trim($_POST['preco'] ?? '');
$localidade = e($_POST['localidade'] ?? '');
$descricao  = trim($_POST['descricao'] ?? '');
$imagemAtual = $anuncio['imagem'];
$categoria = e($_POST['categoria'] ?? '');
$estado    = e($_POST['estado_conservacao'] ?? '');

$newImage = $imagemAtual;

if (!empty($_FILES['imagem']['name'])) {

    $file = $_FILES['imagem'];

    if ($file['error'] === UPLOAD_ERR_OK) {

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg','jpeg','png','webp'];

        if (!in_array($ext, $allowed)) {
            echo json_encode(['success'=>false,'message'=>'Formato de imagem inválido.']);
            exit;
        }

        if ($file['size'] > 5*1024*1024) {
            echo json_encode(['success'=>false,'message'=>'Imagem maior que 5MB.']);
            exit;
        }

        $filename = 'anuncio_' . $id . '_' . time() . '.' . $ext;
        $destPath = __DIR__ . '/../uploads/' . $filename;

        if (!move_uploaded_file($file['tmp_name'], $destPath)) {
            echo json_encode(['success'=>false,'message'=>'Falha ao salvar imagem.']);
            exit;
        }

        $newImage = 'uploads/' . $filename;
    }
}

$sql = "UPDATE anuncios SET 
            titulo = ?, 
            autor = ?, 
            preco = ?, 
            localidade = ?, 
            descricao = ?, 
            imagem = ?,
            categoria = ?,
            estado_conservacao = ?
        WHERE id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param(
    "ssdsssssi",
    $titulo,
    $autor,
    $preco,
    $localidade,
    $descricao,
    $newImage,
    $categoria,
    $estado,
    $id
);

$ok = $stmt->execute();
$stmt->close();

if (!empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
    if ($ok) echo json_encode(['success'=>true,'message'=>'Anúncio atualizado.']);
    else echo json_encode(['success'=>false,'message'=>'Erro ao atualizar.']);
    exit;
}

if ($ok) {
    header('Location: ../perfil.php?msg=atualizado');
    exit;
}

die('Erro ao atualizar.');
