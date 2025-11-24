<?php

session_start();

require_once __DIR__ . '/backend/config.php';

$error_msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = isset($_POST['usuario']) ? trim($_POST['usuario']) : '';
    $senha = isset($_POST['senha']) ? $_POST['senha'] : '';

    if ($usuario === '' || $senha === '') {
        $error_msg = 'Preencha usuário e senha.';
    } else {
        $sql = "SELECT * FROM usuarios WHERE email = ? OR nome = ? LIMIT 1";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("ss", $usuario, $usuario);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result && $result->num_rows === 1) {
                $row = $result->fetch_assoc();

                if (password_verify($senha, $row['senha'])) {
                    $_SESSION['usuario_id'] = $row['id'];
                    $_SESSION['usuario_nome'] = $row['nome'];

                    header("Location: principal.php");
                    exit;
                } else {
                    $error_msg = 'Senha incorreta.';
                }
            } else {
                $error_msg = 'Usuário não encontrado.';
            }

            $stmt->close();
        } else {
            $error_msg = 'Erro interno. Tente novamente.';
        }
    }
}

if ($error_msg === '' && isset($_GET['erro'])) {
    if ($_GET['erro'] === 'senha') $error_msg = 'Senha incorreta.';
    if ($_GET['erro'] === 'usuario') $error_msg = 'Usuário não encontrado.';
}

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <title>Login - Folha Nova</title>
  <link rel="stylesheet" href="assets/css/styleLogin.css">
  <meta name="viewport" content="width=device-width,initial-scale=1">
</head>
<body>
  <div class="container-login">
    <div class="login-box">
      <h2>Login</h2>

      <?php if (!empty($error_msg)): ?>
        <div class="erro"><?php echo htmlspecialchars($error_msg, ENT_QUOTES, 'UTF-8'); ?></div>
      <?php endif; ?>

      <form class="formulario" action="index.php" method="POST" autocomplete="off">
        <label for="usuario">Usuário (email ou nome):</label>
        <input type="text" id="usuario" name="usuario" required>

        <label for="senha">Senha:</label>
        <input type="password" id="senha" name="senha" required>

        <button type="submit">ENTRAR</button>
      </form>

      <li class="cadastro-link"><a id="openRegisterLink">Ainda não possui cadastro?</a></li>
    </div>
  </div>

  <div id="registerModal" class="register-modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);align-items:center;justify-content:center;z-index:9999;">
    <div style="background:#fff;padding:18px;border-radius:10px;max-width:520px;width:94%;box-shadow:0 8px 24px rgba(0,0,0,0.2);position:relative;">
      <button id="closeRegisterModal" style="position:absolute;right:12px;top:8px;border:0;background:transparent;font-size:20px;cursor:pointer;">&times;</button>
      <h3 style="margin-top:0">Crie sua conta</h3>
      <form id="registerForm" method="post" action="backend/cadastro.php">
        <div style="display:flex;flex-direction:column;gap:8px">
          <input name="nome" id="reg_nome" type="text" placeholder="Nome completo" required style="padding:10px;border-radius:6px;border:1px solid #ddd">
          <input name="email" id="reg_email" type="email" placeholder="E-mail" required style="padding:10px;border-radius:6px;border:1px solid #ddd">
          <input name="telefone" id="reg_telefone" type="tel" placeholder="Telefone" style="padding:10px;border-radius:6px;border:1px solid #ddd">
          <input name="senha" id="reg_senha" type="password" placeholder="Senha (min 6 caracteres)" required style="padding:10px;border-radius:6px;border:1px solid #ddd">
          <input name="senha_confirm" id="reg_senha_confirm" type="password" placeholder="Confirmar senha" required style="padding:10px;border-radius:6px;border:1px solid #ddd">
        </div>
        <div id="reg_feedback" style="margin-top:10px;"></div>
        <div style="display:flex;gap:10px;margin-top:12px;justify-content:flex-end">
          <button type="button" id="reg_cancel" style="padding:8px 12px;border-radius:6px;border:1px solid #ccc;background:#fff;cursor:pointer;">Cancelar</button>
          <button type="submit" style="padding:8px 12px;border-radius:6px;border:0;background:#2d7a4b;color:#fff;cursor:pointer;">Cadastrar</button>
        </div>
      </form>
    </div>
  </div>

  <script>
  (function(){
    const openLink = document.getElementById('openRegisterLink');
    const regModal = document.getElementById('registerModal');
    const closeBtn = document.getElementById('closeRegisterModal');
    const cancelBtn = document.getElementById('reg_cancel');
    const feedback = document.getElementById('reg_feedback');
    const form = document.getElementById('registerForm');

    if (openLink) {
      openLink.addEventListener('click', function(e){
        e.preventDefault();
        feedback.innerHTML = '';
        regModal.style.display = 'flex';
      });
    }

    function closeModal(){ regModal.style.display = 'none'; }
    closeBtn.addEventListener('click', closeModal);
    cancelBtn.addEventListener('click', closeModal);
    regModal.addEventListener('click', function(e){ if (e.target === regModal) closeModal(); });

    form.addEventListener('submit', async function(e){
      e.preventDefault(); feedback.innerHTML='';
      const nome = document.getElementById('reg_nome').value.trim();
      const email = document.getElementById('reg_email').value.trim();
      const telefone = document.getElementById('reg_telefone').value.trim();
      const senha = document.getElementById('reg_senha').value;
      const senha_confirm = document.getElementById('reg_senha_confirm').value;

      if (senha.length < 6) { feedback.innerHTML = '<div style="color:#b91c1c">Senha mínima de 6 caracteres.</div>'; return; }
      if (senha !== senha_confirm) { feedback.innerHTML = '<div style="color:#b91c1c">Senhas não conferem.</div>'; return; }

      const fd = new FormData();
      fd.append('nome', nome);
      fd.append('email', email);
      fd.append('telefone', telefone);
      fd.append('senha', senha);

      try {
        const res = await fetch(form.action, { method: 'POST', body: fd });
        const text = await res.text();
        feedback.innerHTML = '<div style="color:#065f46">' + (text || 'Cadastro efetuado com sucesso') + '</div>';
        setTimeout(()=>{ regModal.style.display='none'; form.reset(); }, 1400);
      } catch(err){
        feedback.innerHTML = '<div style="color:#b91c1c">Erro de conexão. Tente novamente.</div>';
      }
    });
  })();
  </script>
</body>
</html>