<?php
include 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $nome = trim($_POST['nome']);
    $email = trim($_POST['email']);
    $telefone = trim($_POST['telefone']);
    $senha = password_hash($_POST['senha'], PASSWORD_DEFAULT);

    $check = $conn->prepare("SELECT id FROM usuarios WHERE email = ? LIMIT 1");
    $check->bind_param("s", $email);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows > 0) {
        echo "E-mail já cadastrado.";
        exit;
    }

    $sql = "INSERT INTO usuarios (nome, email, senha, telefone) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $nome, $email, $senha, $telefone);

    if ($stmt->execute()) {
        echo "Usuário cadastrado com sucesso!";
    } else {
        echo "Erro: " . $stmt->error;
    }
}
?>
