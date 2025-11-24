<?php
session_start();

$possibleKeys = ['usuario_id'];
$userId = null;
foreach ($possibleKeys as $k) {
    if (!empty($_SESSION[$k])) {
        $userId = (int) $_SESSION[$k];
        break;
    }
}

$configCandidates = [
    __DIR__ . '/backend/config.php',
    __DIR__ . '/config.php',
    __DIR__ . '/../config.php'
];

$configFound = false;
foreach ($configCandidates as $p) {
    if (file_exists($p)) {
        require_once $p;
        $configFound = true;
        break;
    }
}

if (!$configFound) {
    http_response_code(500);
    echo "Arquivo de configuração (config.php) não encontrado. Verifique o caminho.";
    exit;
}

if (!isset($conn) || !($conn instanceof mysqli)) {
    http_response_code(500);
    echo "Conexão com o banco indisponível.";
    exit;
}

function e($v) {
    return htmlspecialchars($v ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

$user = null;
$sqlUser = "SELECT id, nome, email, telefone, data_cadastro FROM usuarios WHERE id = ?";
if ($stmt = $conn->prepare($sqlUser)) {
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res && $res->num_rows === 1) {
        $user = $res->fetch_assoc();
    }
    $stmt->close();
}

if (!$user) {
    echo "Usuário não encontrado.";
    exit;
}

$anuncios = [];
$sqlAn = "SELECT id, titulo, autor, descricao, preco, localidade, imagem, data_criacao 
          FROM anuncios 
          WHERE usuario_id = ? 
          ORDER BY id DESC";
if ($stmt2 = $conn->prepare($sqlAn)) {
    $stmt2->bind_param('i', $userId);
    $stmt2->execute();
    $res2 = $stmt2->get_result();
    if ($res2) {
        while ($row = $res2->fetch_assoc()) {
            $anuncios[] = $row;
        }
        $res2->free();
    }
    $stmt2->close();
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <title>Meu Perfil — Folha Nova</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link rel="stylesheet" href="assets/css/styleListar.css">
  <link rel="stylesheet" href="assets/css/stylePerfil.css">
  <link rel="stylesheet" href="assets/css/stylePadrao.css">
</head>
<body>
    <header class="navbar">
        <h1 class="logo">Folha <span>Nova</span></h1>
        <nav>
            <ul>
                <li><a href="principal.php">Início</a></li>
                <li><a href="listar.php">Livros</a></li>
                <li><a href="anuncio.php">Anunciar</a></li>
                <li class="dropdown">
                    <a href="#" class="dropbtn">Perfil</a>
                    <ul class="dropdown-menu">
                        <li><a href="perfil.php">Meu Perfil</a></li>
                        <li><a href="logout.php">Sair</a></li>
                    </ul>
                </li>
            </ul>
        </nav>
    </header>

  <main class="profile-page">
    <div class="container-profile">
      <div class="profile-head">
        <aside class="profile-card" aria-label="Dados do usuário" style="width:100%;">
          <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:12px;">
            <h2 style="margin:0;">Meu Perfil</h2>
            <div>
              <button id="toggleEditBtn" class="btn btn-primary" type="button">Editar</button>
            </div>
          </div>

          <div id="profileView" style="margin-top:12px;">
            <div><strong>Nome:</strong> <?php echo e($user['nome']); ?></div>
            <div><strong>E-mail:</strong> <?php echo e($user['email']); ?></div>
            <div><strong>Telefone:</strong> <?php echo e($user['telefone']); ?></div>
            <div style="margin-top:12px" class="small-muted">ID: <?php echo (int)$user['id']; ?></div>
          </div>

          <div id="profileEdit" style="display:none;margin-top:12px;">
            <form action="backend/editar_perfil.php" method="post" id="editProfileForm">
              <input type="hidden" name="user_id" value="<?php echo (int)$user['id']; ?>">
              
              <label style="display:block;margin-bottom:10px;">
                <div style="font-size:14px;margin-bottom:6px;">Nome</div>
                <input type="text" name="nome" value="<?php echo e($user['nome']); ?>" required>
              </label>

              <label style="display:block;margin-bottom:10px;">
                <div style="font-size:14px;margin-bottom:6px;">E-mail</div>
                <input type="email" name="email" value="<?php echo e($user['email']); ?>" required>
              </label>

              <label style="display:block;margin-bottom:10px;">
                <div style="font-size:14px;margin-bottom:6px;">Telefone</div>
                <input type="tel" name="telefone" value="<?php echo e($user['telefone']); ?>">
              </label>

              <div class="profile-actions" style="display:flex;justify-content:flex-end;gap:8px;margin-top:8px;">
                <button type="button" id="cancelEdit" class="btn" style="background:#f3f4f6;border:1px solid #e5e7eb;">Cancelar</button>
                <button type="submit" class="btn btn-primary">Salvar alterações</button>
              </div>
            </form>
          </div>
        </aside>
      </div>
    </div>

    <div class="container-anuncios">
      <section class="my-anuncios" style="margin-top:28px;">
        <div class="anuncios-header">
          <h2>Meus Anúncios</h2>
          <div class="small-muted"><?php echo count($anuncios); ?> anúncio(s)</div>
        </div>

        <?php if (empty($anuncios)): ?>
          <div class="empty">Você ainda não publicou anúncios. <a href="anuncio.php">Publique o primeiro</a>.</div>
        <?php else: ?>
          <div class="anuncios-container">
            <?php foreach ($anuncios as $a):
                $id = (int)$a['id'];
                $titulo = e($a['titulo']);
                $autor = e($a['autor'] ?: '-');
                $preco = $a['preco'];
                $localidade = e($a['localidade'] ?: '-');
                $descricao = $a['descricao'] ?? '';
                $img_path = 'assets/img/placeholder-book.svg';
                if (!empty($a['imagem'])) {
                    if (preg_match('#^https?://#i', $a['imagem'])) {
                        $img_path = $a['imagem'];
                    } else {
                        $candidate = 'uploads/' . ltrim($a['imagem'], '/');
                        if (file_exists(__DIR__ . '/' . $candidate)) $img_path = $candidate;
                    }
                }
                $preco_text = ($preco === null || $preco === '' || $preco == 0) ? 'A combinar' : 'R$ ' . number_format((float)$preco,2,',','.');
                $published = '-';
                if (!empty($a['data_criacao'])) {
                    $ts = strtotime($a['data_criacao']);
                    if ($ts !== false) $published = date('d/m/Y', $ts);
                }
            ?>
            <article class="card" aria-labelledby="anuncio-<?php echo $id; ?>">
                <div class="thumb" style="background-image:url('<?php echo e($img_path); ?>');"></div>

                <div class="card-body">
                    <h3 id="anuncio-<?php echo $id; ?>" class="title">
                        <?php echo $titulo; ?>
                    </h3>

                    <div class="meta">
                        Autor: <?php echo $autor; ?> — Local: <?php echo $localidade; ?>
                    </div>

                    <div class="desc">
                        <?php echo nl2br(e(
                            (mb_strlen($descricao ?? '', 'UTF-8') > 160)
                            ? mb_substr($descricao, 0, 160, 'UTF-8') . '...'
                            : $descricao
                        )); ?>
                    </div>

                    <div class="card-footer">
                        <div class="info">
                            <div class="price"><?php echo $preco_text; ?></div>
                            <div class="vendedor small-muted">
                                Publicado em: <?php echo e($published); ?>
                            </div>
                        </div>

                        <div class="actions">
                            <a class="btn btn-primary" href="editar_anuncio.php?id=<?php echo (int)$id; ?>">Editar</a>
                        </div>
                    </div>
                </div>
            </article>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </section>
    </div>
  </main>

  <script>
    (function(){
      const toggleBtn = document.getElementById('toggleEditBtn');
      const profileView = document.getElementById('profileView');
      const profileEdit = document.getElementById('profileEdit');
      const cancelBtn = document.getElementById('cancelEdit');

      function showEdit(show) {
        if (show) {
          profileView.style.display = 'none';
          profileEdit.style.display = 'block';
          toggleBtn.textContent = 'Fechar';
        } else {
          profileView.style.display = '';
          profileEdit.style.display = 'none';
          toggleBtn.textContent = 'Editar';
        }
      }

      if (toggleBtn) {
        toggleBtn.addEventListener('click', function(){
          const currentlyHidden = profileEdit.style.display === 'none' || profileEdit.style.display === '';
          showEdit(profileEdit.style.display === 'none' || profileEdit.style.display === '');
        });
      }
      if (cancelBtn) {
        cancelBtn.addEventListener('click', function(){ showEdit(false); });
      }
    })();
  </script>

  <footer>
    <p>&copy; 2025 Folha Nova - Biblioteca Digital</p>
  </footer>
  <script>
    document.addEventListener('DOMContentLoaded', function(){
    const dropdowns = document.querySelectorAll('.navbar .dropdown');

    dropdowns.forEach(dd => {
        const btn = dd.querySelector('.dropbtn');

        dd.classList.remove('open');

        btn.addEventListener('click', function(e){
        e.preventDefault();
        dropdowns.forEach(x => { if (x !== dd) x.classList.remove('open'); });
        dd.classList.toggle('open');
        });

        document.addEventListener('click', function(e){
        if (!e.target.closest('.navbar .dropdown')) {
            dropdowns.forEach(x => x.classList.remove('open'));
        }
        });

        document.addEventListener('keydown', function(e){
        if (e.key === 'Escape') dropdowns.forEach(x => x.classList.remove('open'));
        });
    });
    });
  </script>

</body>
</html>
