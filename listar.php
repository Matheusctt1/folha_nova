<?php

require_once __DIR__ . '/backend/config.php';

function refValues($arr){
    $refs = [];
    foreach($arr as $k => $v) $refs[$k] = &$arr[$k];
    return $refs;
}


$categorias = [
    "Ficção científica",
    "Aventura",
    "Fantasia",
    "Romance",
    "Mistério",
    "Terror",
    "Biografia",
    "Didático",
    "Infantil",
    "HQ",
    "Acadêmico",
    "Autoajuda",
    "História",
    "Ciências",
    "Arte"
];
$estados = [
    "Novo",
    "Quase novo",
    "Usado",
    "Muito usado",
    "Danificado"
];

$busca = trim($_GET['busca'] ?? '');
$precoMin = $_GET['preco_min'] ?? '';
$precoMax = $_GET['preco_max'] ?? '';
$estado = $_GET['estado'] ?? '';
$categoria = $_GET['categoria'] ?? '';

$sql = "SELECT a.id, a.titulo, a.autor, a.descricao, a.preco, a.localidade, a.imagem, a.usuario_id, a.estado_conservacao, a.categoria, u.nome AS vendedor
        FROM anuncios a
        LEFT JOIN usuarios u ON a.usuario_id = u.id
        WHERE 1=1";

$params = [];
$types = "";

if ($busca !== '') {
    $sql .= " AND (a.titulo LIKE ? OR a.descricao LIKE ?)";
    $like = "%{$busca}%";
    $params[] = $like;
    $params[] = $like;
    $types .= "ss";
}

if ($precoMin !== '') {
    $precoMin = str_replace(',', '.', $precoMin);
    if (is_numeric($precoMin)) {
        $sql .= " AND a.preco >= ?";
        $params[] = (float)$precoMin;
        $types .= "d";
    }
}

if ($precoMax !== '') {
    $precoMax = str_replace(',', '.', $precoMax);
    if (is_numeric($precoMax)) {
        $sql .= " AND a.preco <= ?";
        $params[] = (float)$precoMax;
        $types .= "d";
    }
}

if ($estado !== '') {
    $sql .= " AND a.estado_conservacao = ?";
    $params[] = $estado;
    $types .= "s";
}

if ($categoria !== '') {
    $sql .= " AND a.categoria = ?";
    $params[] = $categoria;
    $types .= "s";
}

$sql .= " ORDER BY a.id DESC";

$anuncios = [];
$error = null;

$stmt = $conn->prepare($sql);
if ($stmt === false) {
    $error = $conn->error;
} else {
    if (!empty($params)) {
        $bind_names = [];
        $bind_names[] = $types;
        foreach ($params as $k => $v) $bind_names[] = $v;
        call_user_func_array([$stmt, 'bind_param'], refValues($bind_names));
    }
    if (!$stmt->execute()) {
        $error = $stmt->error;
    } else {
        $res = $stmt->get_result();
        while ($row = $res->fetch_assoc()) $anuncios[] = $row;
        $res->free();
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Folha Nova - Biblioteca</title>
    <link rel="stylesheet" href="assets/css/styleListar.css?v=1">
    <link rel="stylesheet" href="assets/css/stylePadrao.css?v=1">
    <style>
      .filtros {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin: 30px 0px;
        align-items: center;
      }
      .filtros input[type="text"], .filtros input[type="number"], .filtros select { padding:8px; border:1px solid #ccc; border-radius:6px; }
      .filtros .btn { padding:8px 12px; border-radius:6px; cursor:pointer; }
      .filtros .btn--clear { background:transparent; border:1px solid #999; }
    </style>
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

  <div class="page">
    <div class ="anunciosDisponiveis">
      <h1>Anúncios disponíveis</h1>

      <form method="GET" class="filtros" action="listar.php" role="search" aria-label="Filtros de busca">
        <input type="text" name="busca" placeholder="Buscar por título ou palavra-chave"
               value="<?php echo htmlspecialchars($busca, ENT_QUOTES); ?>">

        <input type="number" step="0.01" name="preco_min" placeholder="Preço mínimo"
               value="<?php echo htmlspecialchars($precoMin, ENT_QUOTES); ?>">

        <input type="number" step="0.01" name="preco_max" placeholder="Preço máximo"
               value="<?php echo htmlspecialchars($precoMax, ENT_QUOTES); ?>">

        <select name="estado" aria-label="Estado de conservação">
          <option value="">Estado de conservação</option>
          <?php foreach ($estados as $e): ?>
            <option value="<?php echo htmlspecialchars($e, ENT_QUOTES); ?>" <?php if ($estado === $e) echo 'selected'; ?>>
              <?php echo htmlspecialchars($e, ENT_QUOTES); ?>
            </option>
          <?php endforeach; ?>
        </select>

        <select name="categoria" aria-label="Categoria">
          <option value="">Categoria</option>
          <?php foreach ($categorias as $c): ?>
            <option value="<?php echo htmlspecialchars($c, ENT_QUOTES); ?>" <?php if ($categoria === $c) echo 'selected'; ?>>
              <?php echo htmlspecialchars($c, ENT_QUOTES); ?>
            </option>
          <?php endforeach; ?>
        </select>

        <button type="submit" class="btn btn-primary">Filtrar</button>
        <a href="listar.php" class="btn btn--clear" style="text-decoration:none;padding:8px 10px;border:1px solid #ddd;border-radius:6px;">Limpar</a>
      </form>

    </div>

    <?php if ($error): ?>
      <div class="empty">
        <strong>Erro ao carregar anúncios:</strong><br>
        <?php echo htmlspecialchars($error, ENT_QUOTES); ?>
      </div>
    <?php elseif (empty($anuncios)): ?>
      <div class="empty">Nenhum anúncio encontrado no momento.</div>
    <?php else: ?>
      <div class="anuncios-container">
        <?php foreach ($anuncios as $a):
          $id = (int)$a['id'];
          $titulo = $a['titulo'];
          $autor = $a['autor'];
          $descricao = $a['descricao'];
          $preco = $a['preco'];
          $localidade = $a['localidade'];
          $imagem = $a['imagem'];
          $vendedor = $a['vendedor'] ?: 'Vendedor';
          $categoria_card = $a['categoria'];
          $estado_card = $a['estado_conservacao'];

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


          $short_desc = htmlspecialchars(strlen($descricao) > 160 ? substr($descricao, 0, 160) . '...' : $descricao, ENT_QUOTES);
        ?>
        <article class="card" aria-labelledby="anuncio-<?php echo $id; ?>">
          <div class="thumb" style="background-image:url('<?php echo htmlspecialchars($img_path, ENT_QUOTES); ?>');"></div>
          <div class="card-body">
            <h3 id="anuncio-<?php echo $id; ?>" class="title" style="margin: 0px 0px 10px 0px;"><?php echo htmlspecialchars($titulo, ENT_QUOTES); ?></h3>
            <div class="meta"><strong>Autor:</strong> <?php echo htmlspecialchars($autor ?: '-', ENT_QUOTES); ?></div>
            <div class="meta"><strong>Categoria:</strong> <?php echo htmlspecialchars($categoria_card, ENT_QUOTES); ?></div>
            <div class="meta"><strong>Estado:</strong> <?php echo htmlspecialchars($estado_card, ENT_QUOTES); ?></div>
            <div class="meta" style="margin-top:10px"><strong>Local:</strong> <?php echo htmlspecialchars($localidade ?: '-', ENT_QUOTES); ?></div>
            <div class="footer" style="margin: 10px 0px 0px 0px;">
              <div class="info">
                <div class="price"><?php echo $preco_text; ?></div>
                <div class="vendedor">Vendedor: <?php echo htmlspecialchars($vendedor, ENT_QUOTES); ?></div>
              </div>
              <div class="actions">
                <button class="btn btn-primary" onclick="openContactModal(<?php echo $id; ?>)">Contatar</button>
              </div>
            </div>
          </div>
        </article>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>

  <script>
    function openContactPopup(id) {
      const w = 720;
      const h = 560;
      const left = (window.screen.width / 2) - (w / 2);
      const top = (window.screen.height / 2) - (h / 2);
      const opts = `width=${w},height=${h},left=${left},top=${top},resizable=yes,scrollbars=yes`;
      const url = 'anuncio_popup.php?id=' + encodeURIComponent(parseInt(id, 10));
      window.open(url, 'contatoAnuncio' + id, opts);
    }
  </script>

  <div id="contactModalBackdrop" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.45);align-items:center;justify-content:center;z-index:9999;padding:18px;">
    <div id="contactModal" style="background:#fff;border-radius:10px;max-width:820px;width:100%;box-shadow:0 10px 30px rgba(0,0,0,0.18);position:relative;overflow:auto;max-height:90vh">
      <button id="closeContactModal" style="position:absolute;right:12px;top:8px;border:0;background:transparent;font-size:22px;cursor:pointer;">&times;</button>
      <div id="contactModalContent" style="padding:18px">
        <div style="text-align:center;padding:30px;color:#6b7280">Carregando...</div>
      </div>
    </div>
  </div>

  <script>
    async function openContactModal(id) {
      const backdrop = document.getElementById('contactModalBackdrop');
      const content = document.getElementById('contactModalContent');
      content.innerHTML = '<div style="text-align:center;padding:30px;color:#6b7280">Carregando...</div>';
      backdrop.style.display = 'flex';

      try {
        const resp = await fetch('backend/api_anuncio.php?id=' + encodeURIComponent(parseInt(id,10)));
        const json = await resp.json();

        if (!resp.ok || !json.success) {
          content.innerHTML = '<div style="color:#b91c1c;padding:18px">Erro ao carregar anúncio: ' + (json.message || resp.statusText) + '</div>';
          return;
        }

        const d = json.data;
        const html = `
          <div style="display:flex;gap:22px;flex-wrap:wrap;align-items:flex-start;font-family:system-ui,-apple-system,Segoe UI,Roboto,'Helvetica Neue',Arial;color:#0f172a;">
            <!-- IMAGEM -->
            <div style="flex:0 0 44%;min-width:240px;display:flex;justify-content:center;align-items:flex-start;">
              <div style="width:100%;max-width:420px;border-radius:10px;overflow:hidden;box-shadow:0 6px 22px rgba(9,30,66,0.08);background:#fff">
                <img src="${escapeHtml(d.img_path || '/mnt/data/f9b699ec-fee2-4909-850a-16c98701adcc.png')}"
                    alt="${escapeHtml(d.titulo)}"
                    style="display:block;width:100%;height:100%;max-height:640px;object-fit:cover;">
              </div>
            </div>

            <!-- CONTEÚDO -->
            <div style="flex:1;min-width:300px;display:flex;flex-direction:column">
              <!-- HEADER -->
              <div style="padding:6px 0 10px 0;border-bottom:2px solid #eef2f6;margin-bottom:10px;display:flex;justify-content:space-between;align-items:center;">
                <h2 style="margin:0;font-size:20px;font-weight:700;color:#0b1220;line-height:1.1">${escapeHtml(d.titulo)}</h2>
              </div>

              <!-- META: categoria / estado / autor -->
              <div style="display:flex;flex-wrap:wrap;gap:12px;align-items:center;margin-bottom:12px;">
                <div style="font-size:13px;color:#475569;display:flex;gap:8px;align-items:center">
                  <strong style="color:#111827;">Categoria:</strong>
                  <span style="font-weight:600;color:#0b5f3a">${escapeHtml(d.categoria || '-')}</span>
                </div>

                <div style="font-size:13px;color:#475569;display:flex;gap:8px;align-items:center">
                  <strong style="color:#111827;">Estado:</strong>
                  <span style="font-weight:600;color:#92400e">${escapeHtml(d.estado_conservacao || '-')}</span>
                </div>

                <div style="font-size:13px;color:#475569;display:flex;gap:8px;align-items:center">
                  <strong style="color:#111827;">Autor:</strong>
                  <span style="color:#374151">${escapeHtml(d.autor || '-')}</span>
                </div>
              </div>

              <!-- PREÇO -->
              <div style="margin-bottom:14px">
                <div style="display:inline-block;padding:10px 14px;border-radius:8px;background:#ecfdf5;border:1px solid rgba(6,95,70,0.08);font-weight:700;color:#065f46;">
                  ${escapeHtml(d.preco_text)}
                </div>
              </div>

              <!-- DESCRIÇÃO: caixa delimitada -->
              <div style="background:#f8fafc;border:1px solid #e6eef6;padding:14px;border-radius:8px;color:#374151;line-height:1.5;max-height:220px;overflow:auto;margin-bottom:16px;">
                <div style="font-weight:600;color:#0f172a;margin-bottom:8px;">Descrição</div>
                <div style="white-space:pre-line;">
                  ${nl2br(escapeHtml(d.descricao || 'Nenhuma descrição informada.'))}
                </div>
              </div>

              <!-- DADOS DO ANUNCIANTE: caixa separada -->
              <section style="background:#fff;border:1px solid #e6eef6;padding:14px;border-radius:8px;">
                <h3 style="margin:0 0 12px;font-size:15px;color:#0f172a">Dados do anunciante</h3>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px 20px;align-items:start;font-size:14px;color:#374151">
                  <div>
                    <div style="font-weight:600;color:#111827;margin-bottom:6px;">Nome</div>
                    <div>${escapeHtml(d.vendedor || 'Anunciante')}</div>
                  </div>

                  <div>
                    <div style="font-weight:600;color:#111827;margin-bottom:6px;">Local</div>
                    <div>${escapeHtml(d.localidade || '-')}</div>
                  </div>

                  ${d.vendedor_email ? `
                    <div>
                      <div style="font-weight:600;color:#111827;margin-bottom:6px;">E-mail</div>
                      <div style="word-break:break-all">${escapeHtml(d.vendedor_email)}</div>
                    </div>` : ''}

                  ${d.vendedor_telefone ? `
                    <div>
                      <div style="font-weight:600;color:#111827;margin-bottom:6px;">Telefone</div>
                      <div>${escapeHtml(d.vendedor_telefone)}</div>
                    </div>` : ''}
                </div>
              </section>

              <!-- AÇÕES -->
              <div style="display:flex;justify-content:flex-end;margin-top:14px">
                <button class="btn btn-outline" id="modalCloseBtn" style="padding:8px 12px;border-radius:8px;border:1px solid #e6e9ef;background:#fff">Fechar</button>
              </div>
            </div>
          </div>
        `;


        content.innerHTML = html;

        document.getElementById('modalCloseBtn').addEventListener('click', closeContactModal);
      } catch (err) {
        content.innerHTML = '<div style="color:#b91c1c;padding:18px">Erro de conexão. Tente novamente.</div>';
        console.error(err);
      }
    }

    function closeContactModal() {
      document.getElementById('contactModalBackdrop').style.display = 'none';
    }

    document.getElementById('closeContactModal').addEventListener('click', closeContactModal);
    document.getElementById('contactModalBackdrop').addEventListener('click', function(e){
      if (e.target === this) closeContactModal();
    });

    function escapeHtml(str){
      if (typeof str !== 'string') return '';
      return str.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }
    function nl2br(str){ return str.replace(/\n/g,'<br>'); }
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
            if (!e.target.closest('.navbar .dropdown')) dropdowns.forEach(x => x.classList.remove('open'));
          });
          document.addEventListener('keydown', function(e){
            if (e.key === 'Escape') dropdowns.forEach(x => x.classList.remove('open'));
          });
      });
    });
  </script>
</body>
</html>
