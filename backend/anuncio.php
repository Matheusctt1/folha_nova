<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../login.html');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../anuncio.php');
    exit;
}

$categorias_permitidas = [
    "Ficção científica","Aventura","Fantasia","Romance","Mistério",
    "Terror","Biografia","Didático","Infantil","HQ",
    "Acadêmico","Autoajuda","História","Ciências","Arte"
];

$estados_permitidos = [
    "Novo","Quase novo","Usado","Muito usado","Danificado"
];

$campos_obrigatorios = ['titulo','autor','preco','localidade','categoria','estado_conservacao'];

foreach ($campos_obrigatorios as $c) {
    if (empty($_POST[$c])) {
        $_SESSION['erro'] = "Preencha todos os campos obrigatórios!";
        header('Location: ../anuncio.php');
        exit;
    }
}

if (!isset($_FILES['imagem']) || $_FILES['imagem']['error'] !== 0) {
    $_SESSION['erro'] = "Envie uma imagem válida.";
    header('Location: ../anuncio.php');
    exit;
}

$categoria = $_POST['categoria'];
$estado = $_POST['estado_conservacao'];

if (!in_array($categoria, $categorias_permitidas)) {
    $_SESSION['erro'] = "Categoria inválida.";
    header('Location: ../anuncio.php');
    exit;
}

if (!in_array($estado, $estados_permitidos)) {
    $_SESSION['erro'] = "Estado de conservação inválido.";
    header('Location: ../anuncio.php');
    exit;
}

try {

    $imagem_url = uploadParaImgBB($_FILES['imagem']);
    if (!$imagem_url) {
        throw new Exception("Erro ao enviar imagem para o ImgBB.");
    }

    $titulo = $conn->real_escape_string($_POST['titulo']);
    $autor = $conn->real_escape_string($_POST['autor']);
    $descricao = $conn->real_escape_string($_POST['descricao'] ?? '');
    $localidade = $conn->real_escape_string($_POST['localidade']);
    $preco = floatval($_POST['preco']);
    $usuario_id = intval($_SESSION['usuario_id']);

    $categoriaDB = $conn->real_escape_string($categoria);
    $estadoDB = $conn->real_escape_string($estado);

    $sql = "
        INSERT INTO anuncios 
            (titulo, autor, descricao, preco, localidade, imagem, usuario_id, categoria, estado_conservacao) 
        VALUES 
            ('$titulo', '$autor', '$descricao', $preco, '$localidade', '$imagem_url', $usuario_id, '$categoriaDB', '$estadoDB')
    ";

    if (!$conn->query($sql)) {
        throw new Exception("Erro ao inserir no banco: " . $conn->error);
    }

    $_SESSION['sucesso'] = "Anúncio publicado com sucesso!";
    header("Location: ../listar.php");
    exit;

} catch (Exception $e) {
    $_SESSION['erro'] = "Erro ao publicar anúncio: " . $e->getMessage();
    header("Location: ../anuncio.php");
    exit;
}

function uploadParaImgBB($arquivo) 
{
    $api_key = '5301ec64fecd436aec33aa2a3696499f'; 

    $image_data = base64_encode(file_get_contents($arquivo['tmp_name']));

    $post_data = [
        'key' => $api_key,
        'image' => $image_data,
        'name' => $arquivo['name']
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://api.imgbb.com/1/upload');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);
    curl_close($ch);

    $result = json_decode($response, true);

    if ($result && $result['success']) {
        return $result['data']['url'];
    }

    return false;
}
?>
