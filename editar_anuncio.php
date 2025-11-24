<?php
session_start();

$configCandidates = [
    __DIR__ . '/backend/config.php',
    __DIR__ . '/config.php',
    __DIR__ . '/../config.php'
];
$configFound = false;
foreach ($configCandidates as $p) {
    if (file_exists($p)) { require_once $p; $configFound = true; break; }
}
if (!$configFound) { http_response_code(500); echo "config.php não encontrado."; exit; }

$sessionKeys = ['usuario_id','user_id','id','usuario'];
$usuario_id = null;
foreach ($sessionKeys as $k) {
    if (!empty($_SESSION[$k])) { $usuario_id = (int) $_SESSION[$k]; break; }
}
if (!$usuario_id) {
    header('Location: login.php');
    exit;
}

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($id <= 0) die('ID inválido.');

$stmt = $conn->prepare("SELECT id, titulo, autor, descricao, preco, localidade, imagem, usuario_id, data_criacao FROM anuncios WHERE id = ? LIMIT 1");
if (!$stmt) die('Erro no servidor: ' . $conn->error);
$stmt->bind_param('i', $id);
$stmt->execute();
$anuncio = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$anuncio) die('Anúncio não encontrado.');

if ((int)$anuncio['usuario_id'] !== $usuario_id) {
    die('Acesso negado. Você não é o proprietário deste anúncio.');
}

function e($v){ return htmlspecialchars((string)($v ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }

$img_public = 'assets/img/placeholder-book.svg';
if (!empty($anuncio['imagem'])) {
    if (preg_match('#^https?://#i', $anuncio['imagem'])) {
        $img_public = $anuncio['imagem'];
    } else {
        $candidateFs = __DIR__ . '/' . ltrim($anuncio['imagem'], '/');
        $candidatePublic = ltrim($anuncio['imagem'], '/');
        if (file_exists($candidateFs)) $img_public = $candidatePublic;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8" />
  <title>Editar Anúncio — <?php echo e($anuncio['titulo']); ?></title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link rel="stylesheet" href="assets/css/styleEditarDados.css">
  <style>
    body { background:#f6f7f8; font-family: Inter, Arial, sans-serif; color:#0b1220; margin:0; }
    .container { max-width:980px; margin:28px auto; padding:18px; background:#fff; border-radius:10px; box-shadow:0 8px 24px rgba(0,0,0,0.06); }
    .row { display:flex; gap:18px; flex-wrap:wrap; }
    .col-left { flex:0 0 500px; }
    .col-right { flex:1; min-width:280px; }
    .img-preview { width:100%; height:100%; background-size:cover; background-position:center; border-radius:8px; border:1px solid #eee; }
    .meta-small { color:#6b7280; font-size:13px; margin-top:8px; }
    .form-actions { display:flex; gap:10px; align-items:center; margin-top:12px; }
    .btn { padding:10px 14px; border-radius:8px; font-weight:600; cursor:pointer; }
    .btn-primary { background:#2d7a4b; color:#fff; border:0; }
    .btn-secondary { background:#fff; border:1px solid #d1d5db; }
    .btn-danger { background:#b91c1c; color:#fff; border:0;}
    label { display:block; font-weight:600; margin-bottom:6px; color:#374151; }
    input[type="text"], input[type="number"], textarea { width:100%; box-sizing:border-box; padding:10px; border-radius:6px; border:1px solid #d1d5db; background:#fff; }
  </style>
</head>
<body>
  <main class="container" role="main">
    <h1 style="margin:0 0 8px 0;">Editar Anúncio</h1>
    <p style="margin:0 0 16px 0;color:#6b7280;">Altere os dados do anúncio e clique em salvar.</p>

    <div class="row">
      <div class="col-left">
        <div class="img-preview" style="background-image:url('<?php echo e($img_public); ?>');"></div>
      </div>

      <div class="col-right">
        <form id="formEditar" action="backend/anuncio_update.php" method="POST" enctype="multipart/form-data">
          <input type="hidden" name="id" value="<?php echo (int)$anuncio['id']; ?>">

          <div class="form-group" style="margin-bottom:12px;">
            <label for="titulo">Título</label>
            <input id="titulo" type="text" name="titulo" required value="<?php echo e($anuncio['titulo']); ?>">
          </div>

          <div class="form-group" style="margin-bottom:12px;">
            <label for="autor">Autor</label>
            <input id="autor" type="text" name="autor" required value="<?php echo e($anuncio['autor']); ?>">
          </div>

          <div class="form-group" style="margin-bottom:12px;">
            <label for="preco">Preço (R$)</label>
            <input id="preco" type="number" step="0.01" name="preco" value="<?php echo e($anuncio['preco']); ?>">
          </div>

          <div class="form-group" style="margin-bottom:12px;">
            <label for="localidade">Localidade</label>
            <input id="localidade" type="text" name="localidade" value="<?php echo e($anuncio['localidade']); ?>">
          </div>
          <!-- Categoria -->
          <div class="form-group" style="margin-bottom:12px;">
              <label for="categoria">Categoria</label>
              <select id="categoria" name="categoria" required style="padding:10px;border:1px solid #d1d5db;border-radius:6px;width:100%;">
                  <?php
                  $categorias = [
                      "Ficção científica","Aventura","Fantasia","Romance","Mistério",
                      "Terror","Biografia","Didático","Infantil","HQ",
                      "Acadêmico","Autoajuda","História","Ciências","Arte"
                  ];
                  foreach ($categorias as $c):
                  ?>
                      <option value="<?php echo e($c); ?>"
                          <?php if (($anuncio['categoria'] ?? '') === $c) echo 'selected'; ?>>
                          <?php echo e($c); ?>
                      </option>
                  <?php endforeach; ?>
              </select>
                  </div>

          <div class="form-group" style="margin-bottom:12px;">
              <label for="estado_conservacao">Estado de conservação</label>
              <select id="estado_conservacao" name="estado_conservacao" required style="padding:10px;border:1px solid #d1d5db;border-radius:6px;width:100%;">
                  <?php
                  $estados = [
                      "Novo","Quase novo","Usado","Muito usado","Danificado"
                  ];
                  foreach ($estados as $e):
                  ?>
                      <option value="<?php echo e($e); ?>"
                          <?php if (($anuncio['estado_conservacao'] ?? '') === $e) echo 'selected'; ?>>
                          <?php echo e($e); ?>
                      </option>
                  <?php endforeach; ?>
              </select>
          </div>
          <div class="form-group" style="margin-bottom:12px;">
              <label for="descricao">Descrição</label>
              <textarea id="descricao" name="descricao" rows="6"><?php echo e($anuncio['descricao']); ?></textarea>
          </div>

          <div class="form-actions">
            <button type="submit" class="btn btn-primary">Salvar alterações</button>
            <button type="button" id="btnExcluir" class="btn btn-danger">Excluir anúncio</button>
            <a class="btn btn-secondary" href="perfil.php" style="text-decoration:none;display:inline-flex;align-items:center;justify-content:center;margin-left:auto">Cancelar</a>
          </div>
        </form>
      </div>
    </div>
  </main>

<script>
(function(){
  const form = document.getElementById('formEditar');
  if (form) {
    form.addEventListener('submit', async function(e){
      e.preventDefault();
      const btn = form.querySelector('button[type="submit"]');
      if (btn) btn.disabled = true;

      const fd = new FormData(form);
      try {
        const resp = await fetch(form.action, {
          method: 'POST',
          body: fd,
          headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        const data = await resp.json().catch(()=>({ success: resp.ok, message: '' }));
        if (data && data.success) {
          location.href = 'perfil.php?msg=atualizado';
        } else {
          alert(data.message || 'Erro ao atualizar o anúncio.');
        }
      } catch (err) {
        console.error(err);
        alert('Erro de conexão. Tente novamente.');
      } finally {
        if (btn) btn.disabled = false;
      }
    });
  }

  const btnExcluir = document.getElementById('btnExcluir');
  if (btnExcluir) {
    btnExcluir.addEventListener('click', async function(){
      if (!confirm('Confirma exclusão deste anúncio? Esta ação não pode ser desfeita.')) return;
      btnExcluir.disabled = true;

      const id = <?php echo (int)$anuncio['id']; ?>;
      try {
        const body = new URLSearchParams();
        body.append('id', id);

        const resp = await fetch('backend/anuncio_delete.php', {
          method: 'POST',
          body: body.toString(),
          headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' }
        });

        const data = await resp.json().catch(()=>({ success: resp.ok, message: '' }));
        if (data && data.success) {
          location.href = 'perfil.php?msg=deletado';
        } else {
          alert(data.message || 'Falha ao excluir o anúncio.');
        }
      } catch (err) {
        console.error(err);
        alert('Erro de conexão.');
      } finally {
        btnExcluir.disabled = false;
      }
    });
  }
})();
</script>
</body>
</html>