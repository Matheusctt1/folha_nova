<?php

session_start();

if (!isset($_SESSION['usuario_id'])) {
    header('Location: index.php');
    exit;
}

$configPath = __DIR__ . '/backend/config.php';
if (file_exists($configPath)) {
    require_once $configPath;
}

function e($s) {
    return htmlspecialchars((string)$s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Criar Anúncio - Folha Nova</title>
  <link rel="stylesheet" href="assets/css/styleAnuncio.css">
  <link rel="stylesheet" href="assets/css/stylePadrao.css">
</head>
<body>
    <!-- Navbar -->
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

    <main>
        <div class="card">
            <h1>Criar Anúncio</h1>

            <?php if (isset($_GET['msg'])): ?>
                <div class="info"><?php echo e($_GET['msg']); ?></div>
            <?php endif; ?>

            <form action="backend/anuncio.php" method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="titulo">Título do Anúncio</label>
                    <input type="text" id="titulo" name="titulo" required value="<?php echo isset($_GET['titulo']) ? e($_GET['titulo']) : ''; ?>">
                </div>

                <div class="form-group">
                    <label for="autor">Autor do Livro</label>
                    <input type="text" id="autor" name="autor" required value="<?php echo isset($_GET['autor']) ? e($_GET['autor']) : ''; ?>">
                </div>

                <div class="form-group">
                    <label for="preco">Preço (R$)</label>
                    <input type="number" id="preco" name="preco" step="0.01" required value="<?php echo isset($_GET['preco']) ? e($_GET['preco']) : ''; ?>">
                </div>

                <div class="form-group">
                    <label for="localidade">Localidade (Cidade - Estado)</label>
                    <input type="text" id="localidade" name="localidade" placeholder="Ex: Porto Alegre - RS" required value="<?php echo isset($_GET['localidade']) ? e($_GET['localidade']) : ''; ?>">
                </div>

                <div class="form-group">
                    <label for="categoria">Categoria</label>
                    <select id="categoria" name="categoria" required>
                        <?php
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
                        $selCat = $_GET['categoria'] ?? '';
                        foreach ($categorias as $cat): ?>
                            <option value="<?php echo e($cat); ?>" <?php if ($selCat === $cat) echo 'selected'; ?>>
                                <?php echo e($cat); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="estado_conservacao">Estado de conservação</label>
                    <select id="estado_conservacao" name="estado_conservacao" required>
                        <?php
                        $estados = [
                            "Novo",
                            "Quase novo",
                            "Usado",
                            "Muito usado",
                            "Danificado"
                        ];
                        $selEst = $_GET['estado_conservacao'] ?? '';
                        foreach ($estados as $est): ?>
                            <option value="<?php echo e($est); ?>" <?php if ($selEst === $est) echo 'selected'; ?>>
                                <?php echo e($est); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="descricao">Descrição</label>
                    <textarea id="descricao" name="descricao" rows="4"><?php echo isset($_GET['descricao']) ? e($_GET['descricao']) : ''; ?></textarea>
                </div>

                <div class="form-group">
                    <label for="imagem">Imagem do Livro</label>
                    <input type="file" id="imagem" name="imagem" accept="image/*" required>
                </div>

                <button type="submit">Publicar</button>
            </form>

        </div>
    </main>
    <footer>
        <p>&copy; <?php echo date('Y'); ?> Folha Nova - Biblioteca Digital</p>
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
