<?php
require_once __DIR__ . '/backend/config.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($id <= 0) {
    echo "ID inválido.";
    exit;
}

$sql = "SELECT a.*, u.nome AS vendedor, u.email AS vendedor_email, u.telefone AS vendedor_telefone
        FROM anuncios a
        LEFT JOIN usuarios u ON a.usuario_id = u.id
        WHERE a.id = ?
        LIMIT 1";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo "Erro no servidor: " . htmlspecialchars($conn->error);
    exit;
}
$stmt->bind_param("i", $id);
$stmt->execute();
$res = $stmt->get_result();
$anuncio = $res->fetch_assoc();
$stmt->close();

if (!$anuncio) {
    echo "Anúncio não encontrado.";
    exit;
}

$titulo = $anuncio['titulo'];
$autor = $anuncio['autor'];
$descricao = $anuncio['descricao'];
$preco = $anuncio['preco'];
$localidade = $anuncio['localidade'];
$imagem = $anuncio['imagem'];
$vendedor = $anuncio['vendedor'] ?: 'Vendedor';
$vendedor_email = $anuncio['vendedor_email'] ?? '';
$vendedor_telefone = $anuncio['vendedor_telefone'] ?? '';

$img_path = 'assets/img/placeholder-book.svg';
if (!empty($imagem)) {
    if (preg_match('#^https?://#i', $imagem)) {
        $img_path = $imagem;
    } else {
        $candidate = 'uploads/' . $imagem;
        $img_path = file_exists(__DIR__ . '/' . $candidate) ? $candidate : $img_path;
    }
}

$preco_text = ($preco === null || $preco === '' || $preco == 0)
    ? 'A combinar'
    : 'R$ ' . number_format((float)$preco, 2, ',', '.');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8" />
  <title><?php echo htmlspecialchars($titulo,ENT_QUOTES); ?> - Contato</title>
  <link rel="stylesheet" href="assets/css/stylePopup.css">
</head>
<body>
  <div class="popup">
    <header class="popup-header">
      <h2><?php echo htmlspecialchars($titulo,ENT_QUOTES); ?></h2>
      <button onclick="window.close()" class="close-btn" aria-label="Fechar">×</button>
    </header>

    <div class="popup-body">
      <div class="left">
        <div class="img" style="background-image:url('<?php echo htmlspecialchars($img_path, ENT_QUOTES); ?>')"></div>
      </div>

      <div class="right">
        <div class="meta"><strong>Autor:</strong> <?php echo htmlspecialchars($autor ?: '-', ENT_QUOTES); ?></div>
        <div class="meta"><strong>Local:</strong> <?php echo htmlspecialchars($localidade ?: '-', ENT_QUOTES); ?></div>
        <div class="price"><?php echo $preco_text; ?></div>
        <hr>
        <div class="descricao">
          <?php echo nl2br(htmlspecialchars($descricao, ENT_QUOTES)); ?>
        </div>

        <section class="vendedor">
          <h3>Dados do anunciante</h3>
          <p><strong>Nome:</strong> <?php echo htmlspecialchars($vendedor, ENT_QUOTES); ?></p>
          <?php if (!empty($vendedor_email)): ?>
            <p><strong>E-mail:</strong> <a href="mailto:<?php echo htmlspecialchars($vendedor_email, ENT_QUOTES); ?>"><?php echo htmlspecialchars($vendedor_email, ENT_QUOTES); ?></a></p>
          <?php endif; ?>
          <?php if (!empty($vendedor_telefone)): ?>
            <p><strong>Telefone:</strong> <a href="tel:<?php echo htmlspecialchars($vendedor_telefone, ENT_QUOTES); ?>"><?php echo htmlspecialchars($vendedor_telefone, ENT_QUOTES); ?></a></p>
          <?php endif; ?>
        </section>

        <div class="popup-actions">
          <a class="btn btn-primary" href="mailto:<?php echo htmlspecialchars($vendedor_email, ENT_QUOTES); ?>">Enviar e-mail</a>
          <?php if (!empty($vendedor_telefone)): ?>
            <a class="btn btn-secondary" href="tel:<?php echo htmlspecialchars($vendedor_telefone, ENT_QUOTES); ?>">Ligar</a>
          <?php endif; ?>
          <button class="btn btn-outline" onclick="window.close()">Fechar</button>
        </div>
      </div>
    </div>
  </div>
</body>
</html>
