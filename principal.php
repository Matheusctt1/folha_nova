<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header('Location: index.php');
    exit;
}

$configPath = __DIR__ . '/backend/config.php';
$anuncios = [];

if (file_exists($configPath)) {
    require_once $configPath;
    if (isset($conn) && is_object($conn)) {
        $sql = "SELECT id, titulo, autor, descricao, preco, localidade, imagem, usuario_id, data_criacao,
                       categoria, estado_conservacao
                FROM anuncios
                ORDER BY id DESC
                LIMIT 3";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->execute();
            $res = $stmt->get_result();
            while ($row = $res->fetch_assoc()) {
                $anuncios[] = $row;
            }
            $stmt->close();
        }
    }
}

function e($s) {
    return htmlspecialchars((string)$s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Folha Nova - Biblioteca</title>
    <link rel="stylesheet" href="assets/css/styleIndex.css">
    <link rel="stylesheet" href="assets/css/stylePadrao.css">
    <style>
        .book-list { display:flex; gap:18px; flex-wrap:wrap; justify-content:center; }
        .book-card {
            width: 30%;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 6px 18px rgba(0,0,0,0.06);
            background: #fff;
            display: flex;
            flex-direction: column;
        }
        .book-card img { width:100%; height:340px; object-fit:cover; display:block; }
        .book-info { padding:12px; display:flex; flex-direction:column; flex:1; }
        .book-info h3 { margin:0 0 6px; font-size:16px; color:#0f172a; }
        .book-info .autor {color:#475569; font-size:13px; }
        .book-info .price { margin:0 0 8px; font-weight:700; color:#065f46; }
        .book-info .location { margin:10px 0 6px; font-size:13px; color:#6b7280; }
        .book-info .date { margin-top:auto; font-size:12px; color:#94a3b8; }
        .book-info .extra {font-size:13px; color:#374151; }
        .btn { display:inline-block; padding:8px 12px; border-radius:8px; background:#0b5f3a; color:#fff; border:0; cursor:pointer; text-decoration:none; }
        .btn.btn-primary { background:#0b5f3a; }
    </style>
</head>
<body>
    <header class="navbar">
        <h1 class="logo">Folha <span>Nova</span></h1>

        <nav class="nav-main">
            <ul class="nav-list">
                <li><a href="principal.php">Início</a></li>
                <li><a href="listar.php">Livros</a></li>
                <li><a href="anuncio.php">Anunciar</a></li>
                <li class="dropdown nav-profile">
                    <a href="#" class="dropbtn">Perfil</a>
                    <ul class="dropdown-menu">
                        <li><a href="perfil.php">Meu Perfil</a></li>
                        <li><a href="logout.php">Sair</a></li>
                    </ul>
                </li>
            </ul>
        </nav>

        <div class="dropdown mobile-dropdown" aria-haspopup="true" aria-expanded="false">
            <button class="menu-icon" aria-label="Abrir menu">☰</button>
            <ul class="dropdown-menu mobile-menu" aria-hidden="true">
                <li><a href="principal.php">Início</a></li>
                <li><a href="listar.php">Livros</a></li>
                <li><a href="anuncio.php">Anunciar</a></li>
                <li><a href="perfil.php">Meu Perfil</a></li>
                <li><a href="logout.php">Sair</a></li>
            </ul>
        </div>
    </header>

    <section class="hero">
        <div class="hero-text">
            <h2>Descubra livros incríveis</h2>
            <p>Literatura para todas as idades</p>
            <a href="listar.php" class="btn">Ver Acervo</a>
        </div>
    </section>

    <section class="books">
        <h2>Novos Lançamentos</h2>
        <div class="book-list" id="anuncios-container">
            <?php if (!empty($anuncios)): ?>
                <?php foreach ($anuncios as $anuncio):
                    $id = (int) $anuncio['id'];
                    $titulo = $anuncio['titulo'] ?? 'Título não informado';
                    $autor = $anuncio['autor'] ?? 'Autor não informado';
                    $localidade = $anuncio['localidade'] ?? 'Local não informado';
                    $fallbackImg = '/mnt/data/f9b699ec-fee2-4909-850a-16c98701adcc.png';
                    $imgSrc = !empty($anuncio['imagem']) ? $anuncio['imagem'] : $fallbackImg;
                    $precoRaw = $anuncio['preco'];
                    $precoText = (is_null($precoRaw) || $precoRaw === '' || floatval($precoRaw) == 0)
                        ? 'A combinar'
                        : 'R$ ' . number_format((float)$precoRaw, 2, ',', '.');
                    $categoria = $anuncio['categoria'] ?? '-';
                    $estado = $anuncio['estado_conservacao'] ?? '-';
                ?>
                    <div class="book-card" role="article" aria-labelledby="book-<?php echo $id; ?>">
                        <img src="<?php echo e($imgSrc); ?>" alt="<?php echo e($titulo); ?>" onerror="this.onerror=null;this.src='<?php echo $fallbackImg; ?>'">
                        <div class="book-info">
                            <h3 id="book-<?php echo $id; ?>"><?php echo e($titulo); ?></h3>
                            <p class="autor"><strong>Autor:</strong> <?php echo e($autor); ?></p>

                            <p class="extra">
                                <strong>Categoria:</strong> <?php echo e($categoria); ?><br>
                                <strong>Estado:</strong> <?php echo e($estado); ?>
                            </p>
                            <p class="location"><?php echo e($localidade); ?></p>
                            <p class="price"><?php echo e($precoText); ?></p>
                            <p class="date">Publicado recentemente</p>

                            <div style="margin-top:10px;">
                                <button class="btn btn-primary contact-btn" data-id="<?php echo $id; ?>">Contatar</button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="loading">Carregando anúncios.</div>
            <?php endif; ?>
        </div>

        <div style="text-align: center; margin-top: 30px;">
            <a href="listar.php" class="btn">Visualizar Todos os Anúncios</a>
        </div>
    </section>

    <footer>
        <p>&copy; <?php echo date('Y'); ?> Folha Nova - Biblioteca Digital</p>
    </footer>

    <div id="contactModalBackdrop"
        style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.45);align-items:center;justify-content:center;z-index:9999;padding:18px;">
        <div id="contactModal"
            style="background:#fff;border-radius:10px;max-width:920px;width:100%;box-shadow:0 10px 30px rgba(0,0,0,0.18);position:relative;overflow:auto;max-height:92vh">
            <button id="closeContactModal"
                    style="position:absolute;right:12px;top:8px;border:0;background:transparent;font-size:22px;cursor:pointer;">&times;</button>
            <div id="contactModalContent" style="padding:18px">
                <div style="text-align:center;padding:30px;color:#6b7280">Carregando.</div>
            </div>
        </div>
    </div>

    <script>
    (function () {
        function escapeHtml(str) {
            if (typeof str !== 'string') return '';
            return str.replace(/&/g,'&amp;')
                      .replace(/</g,'&lt;')
                      .replace(/>/g,'&gt;')
                      .replace(/"/g,'&quot;')
                      .replace(/'/g,'&#39;');
        }
        function nl2br(str) { return (str || '').replace(/\n/g,'<br>'); }

        document.addEventListener('DOMContentLoaded', function () {
            const navDropdowns = Array.from(document.querySelectorAll('.navbar .dropdown'));
            function closeAllNavDropdowns() { navDropdowns.forEach(x => x.classList.remove('open')); }
            navDropdowns.forEach(dd => {
                const btn = dd.querySelector('.dropbtn');
                dd.classList.remove('open');
                if (!btn) return;
                btn.addEventListener('click', function (e) {
                    e.preventDefault();
                    navDropdowns.forEach(x => { if (x !== dd) x.classList.remove('open'); });
                    dd.classList.toggle('open');
                });
            });

            const mobileDropdown = document.querySelector('.mobile-dropdown');
            const menuIcon = mobileDropdown ? mobileDropdown.querySelector('.menu-icon') : null;
            const mobileMenu = mobileDropdown ? mobileDropdown.querySelector('.mobile-menu') : null;

            function openMobileMenu() {
                if (!mobileDropdown) return;
                mobileDropdown.classList.add('open');
                mobileDropdown.setAttribute('aria-expanded', 'true');
                if (mobileMenu) mobileMenu.setAttribute('aria-hidden', 'false');
            }
            function closeMobileMenu() {
                if (!mobileDropdown) return;
                mobileDropdown.classList.remove('open');
                mobileDropdown.setAttribute('aria-expanded', 'false');
                if (mobileMenu) mobileMenu.setAttribute('aria-hidden', 'true');
            }
            function toggleMobileMenu(e) {
                if (!mobileDropdown) return;
                const isOpen = mobileDropdown.classList.toggle('open');
                mobileDropdown.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
                if (mobileMenu) mobileMenu.setAttribute('aria-hidden', isOpen ? 'false' : 'true');
                if (e && typeof e.stopPropagation === 'function') e.stopPropagation();
            }
            if (menuIcon) {
                menuIcon.addEventListener('click', toggleMobileMenu);
                menuIcon.addEventListener('keydown', function (ev) {
                    if (ev.key === 'Enter' || ev.key === ' ') {
                        ev.preventDefault();
                        toggleMobileMenu();
                    }
                });
            }
            document.addEventListener('click', function (ev) {
                if (!ev.target.closest('.navbar .dropdown') && !ev.target.closest('.mobile-dropdown')) {
                    closeAllNavDropdowns();
                }
                if (mobileDropdown && !mobileDropdown.contains(ev.target)) {
                    closeMobileMenu();
                }
            });
            document.addEventListener('keydown', function (ev) {
                if (ev.key === 'Escape') {
                    closeAllNavDropdowns();
                    closeMobileMenu();
                }
            });

            const backdrop = document.getElementById('contactModalBackdrop');
            const content = document.getElementById('contactModalContent');
            const closeBtn = document.getElementById('closeContactModal');

            async function openContactModal(id) {
                if (!backdrop || !content) return;
                content.innerHTML = '<div style="text-align:center;padding:30px;color:#6b7280">Carregando.</div>';
                backdrop.style.display = 'flex';

                try {
                    const resp = await fetch('backend/api_anuncio.php?id=' + encodeURIComponent(parseInt(id, 10)));
                    const json = await resp.json();

                    if (!resp.ok || !json.success) {
                        content.innerHTML = '<div style="color:#b91c1c;padding:18px">Erro ao carregar anúncio.</div>';
                        return;
                    }

                    const d = json.data || {};

                    const img_path = d.img_path || '<?php echo addslashes('/mnt/data/f9b699ec-fee2-4909-850a-16c98701adcc.png'); ?>';
                    const titulo = escapeHtml(d.titulo || '-');
                    const autor = escapeHtml(d.autor || '-');
                    const localidade = escapeHtml(d.localidade || '-');
                    const preco_text = escapeHtml(d.preco_text || 'A combinar');
                    const descricao = nl2br(escapeHtml(d.descricao || ''));
                    const vendedor = escapeHtml(d.vendedor || 'Anunciante');
                    const vendedor_email = escapeHtml(d.vendedor_email || '');
                    const vendedor_telefone = escapeHtml(d.vendedor_telefone || '');
                    const categoria = escapeHtml(d.categoria || '-');
                    const estado = escapeHtml(d.estado_conservacao || '-');

                    content.innerHTML = `
                      <div style="display:flex;gap:22px;flex-wrap:wrap;align-items:flex-start;font-family:system-ui,-apple-system,Segoe UI,Roboto,'Helvetica Neue',Arial;color:#0f172a;">
                        <div style="flex:0 0 44%;min-width:240px;display:flex;justify-content:center;align-items:flex-start;">
                          <div style="width:100%;max-width:420px;border-radius:10px;overflow:hidden;box-shadow:0 6px 22px rgba(9,30,66,0.08);background:#fff">
                            <img src="${img_path}" alt="${titulo}" style="display:block;width:100%;height:100%;max-height:640px;object-fit:cover;">
                          </div>
                        </div>

                        <div style="flex:1;min-width:300px;display:flex;flex-direction:column">
                          <div style="padding:6px 0 10px 0;border-bottom:2px solid #eef2f6;margin-bottom:10px;display:flex;justify-content:space-between;align-items:center;">
                            <h2 style="margin:0;font-size:20px;font-weight:700;color:#0b1220;line-height:1.1">${titulo}</h2>
                          </div>

                          <div style="display:flex;flex-wrap:wrap;gap:12px;align-items:center;margin-bottom:12px;">
                            <div style="font-size:13px;color:#475569;display:flex;gap:8px;align-items:center">
                              <strong style="color:#111827;x">Categoria:</strong>
                              <span style="font-weight:600;color:#0b5f3a">${categoria}</span>
                            </div>

                            <div style="font-size:13px;color:#475569;display:flex;gap:8px;align-items:center">
                              <strong style="color:#111827;">Estado:</strong>
                              <span style="font-weight:600;color:#92400e">${estado}</span>
                            </div>

                            <div style="font-size:13px;color:#475569;display:flex;gap:8px;align-items:center">
                              <strong style="color:#111827;">Autor:</strong>
                              <span style="color:#374151">${autor}</span>
                            </div>
                          </div>

                          <div style="margin-bottom:14px">
                            <div style="display:inline-block;padding:10px 14px;border-radius:8px;background:#ecfdf5;border:1px solid rgba(6,95,70,0.08);font-weight:700;color:#065f46;">
                              ${preco_text}
                            </div>
                          </div>

                          <div style="background:#f8fafc;border:1px solid #e6eef6;padding:14px;border-radius:8px;color:#374151;line-height:1.5;max-height:220px;overflow:auto;margin-bottom:16px;">
                            <div style="font-weight:600;color:#0f172a;margin-bottom:8px;">Descrição</div>
                            <div style="white-space:pre-line;">${descricao}</div>
                          </div>

                          <section style="background:#fff;border:1px solid #e6eef6;padding:14px;border-radius:8px;">
                            <h3 style="margin:0 0 12px;font-size:15px;color:#0f172a">Dados do anunciante</h3>

                            <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px 20px;align-items:start;font-size:14px;color:#374151">
                              <div>
                                <div style="font-weight:600;color:#111827;margin-bottom:6px;">Nome</div>
                                <div>${vendedor}</div>
                              </div>

                              <div>
                                <div style="font-weight:600;color:#111827;margin-bottom:6px;">Local</div>
                                <div>${localidade}</div>
                              </div>

                              ${vendedor_email ? `
                                <div>
                                  <div style="font-weight:600;color:#111827;margin-bottom:6px;">E-mail</div>
                                  <div style="word-break:break-all">${vendedor_email}</div>
                                </div>` : ''}

                              ${vendedor_telefone ? `
                                <div>
                                  <div style="font-weight:600;color:#111827;margin-bottom:6px;">Telefone</div>
                                  <div>${vendedor_telefone}</div>
                                </div>` : ''}
                            </div>
                          </section>
                        </div>
                      </div>
                    `;
                } catch (err) {
                    content.innerHTML = '<div style="color:#b91c1c;padding:18px">Erro de conexão.</div>';
                    console.error(err);
                }
            }

            function closeContactModal() {
                if (!backdrop) return;
                backdrop.style.display = 'none';
            }

            if (closeBtn) closeBtn.addEventListener('click', closeContactModal);
            if (backdrop) {
                backdrop.addEventListener('click', function (e) {
                    if (e.target === this) closeContactModal();
                });
            }

            document.querySelectorAll('.contact-btn').forEach(btn => {
                btn.addEventListener('click', function () {
                    const id = this.getAttribute('data-id');
                    openContactModal(id);
                });
            });

            if (mobileDropdown) {
                mobileDropdown.setAttribute('aria-expanded', mobileDropdown.classList.contains('open') ? 'true' : 'false');
                if (mobileMenu) mobileMenu.setAttribute('aria-hidden', mobileDropdown.classList.contains('open') ? 'false' : 'true');
            }
        });
    })();
    </script>
</body>
</html>
