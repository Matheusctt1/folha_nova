<?php

session_start();
require_once __DIR__ . '/config.php';

header('Content-Type: application/json; charset=utf-8');

$sessionKeys = ['usuario_id','user_id','id','usuario'];
$usuario_id = null;
foreach ($sessionKeys as $k) {
    if (!empty($_SESSION[$k])) { $usuario_id = (int) $_SESSION[$k]; break; }
}

function respond_json($ok, $msg, $extra = []) {
    $out = array_merge(['success' => $ok, 'message' => $msg], $extra);
    echo json_encode($out);
    exit;
}

$isAjax = (
    !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
    strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'
);

if (!$usuario_id) {
    if ($isAjax) respond_json(false, 'Acesso negado. Faça login.');
    header('Location: ../login.php');
    exit;
}

$input_user_id = isset($_POST['user_id']) ? (int) $_POST['user_id'] : 0;
$nome = isset($_POST['nome']) ? trim($_POST['nome']) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$telefone = isset($_POST['telefone']) ? trim($_POST['telefone']) : '';

if ($input_user_id <= 0 || $input_user_id !== $usuario_id) {
    if ($isAjax) respond_json(false, 'ID do usuário inválido.');
    header('Location: ../perfil.php?erro=usuario');
    exit;
}

if ($nome === '' || $email === '') {
    if ($isAjax) respond_json(false, 'Nome e e-mail são obrigatórios.');
    header('Location: ../perfil.php?erro=campos');
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    if ($isAjax) respond_json(false, 'E-mail inválido.');
    header('Location: ../perfil.php?erro=email');
    exit;
}

$stmt = $conn->prepare("SELECT id FROM usuarios WHERE email = ? AND id <> ? LIMIT 1");
if (!$stmt) {
    if ($isAjax) respond_json(false, 'Erro no servidor.');
    header('Location: ../perfil.php?erro=server');
    exit;
}
$stmt->bind_param('si', $email, $usuario_id);
$stmt->execute();
$res = $stmt->get_result();
if ($res && $res->num_rows > 0) {
    $stmt->close();
    if ($isAjax) respond_json(false, 'E-mail já cadastrado por outro usuário.');
    header('Location: ../perfil.php?erro=email_duplicado');
    exit;
}
$stmt->close();

$stmt = $conn->prepare("UPDATE usuarios SET nome = ?, email = ?, telefone = ? WHERE id = ?");
if (!$stmt) {
    if ($isAjax) respond_json(false, 'Erro no servidor ao atualizar.');
    header('Location: ../perfil.php?erro=server2');
    exit;
}
$stmt->bind_param('sssi', $nome, $email, $telefone, $usuario_id);
$ok = $stmt->execute();
$stmt->close();

if (!$ok) {
    if ($isAjax) respond_json(false, 'Falha ao atualizar perfil.');
    header('Location: ../perfil.php?erro=update_fail');
    exit;
}

if (!empty($_SESSION['usuario_nome'])) $_SESSION['usuario_nome'] = $nome;
if (!empty($_SESSION['usuario_email'])) $_SESSION['usuario_email'] = $email;

if ($isAjax) {
    respond_json(true, 'Perfil atualizado com sucesso.');
} else {
    header('Location: ../perfil.php?msg=perfil_atualizado');
    exit;
}
