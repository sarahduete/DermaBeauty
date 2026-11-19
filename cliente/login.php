<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../admin/config/conecta.php';

$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';

    $stmt = $pdo->prepare(
        'SELECT id, nome, senha FROM clientes WHERE email = :email'
    );

    $stmt->execute([':email' => $email]);
    $cliente = $stmt->fetch();

    if ($cliente && password_verify($senha, $cliente['senha'])) {
        session_regenerate_id(true);

        $_SESSION['cliente_id'] = $cliente['id'];
        $_SESSION['cliente_nome'] = $cliente['nome'];

        header('Location: meus-agendamentos.php');
        exit;
    }

    $erro = 'E-mail ou senha incorretos.';
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Entrar | Derma Beauty</title>
    <link rel="stylesheet" href="../css/style.css">
</head>

<body class="pagina-login-cliente">

    <main class="login-container">

        <section class="login-card">

            <div class="login-marca">
                <span class="login-linha"></span>
                <span>DERMA BEAUTY</span>
                <span class="login-linha"></span>
            </div>

            <h1>Seja Bem-vinda!</h1>

            <p class="login-descricao">
                Acesse sua conta e continue cuidando de você.
            </p>

            <?php if (isset($_GET['cadastro'])): ?>
                <p class="login-sucesso">
                    Cadastro realizado com sucesso! Faça seu primeiro acesso.
                </p>
            <?php endif; ?>

            <?php if ($erro): ?>
                <p class="login-erro">
                    <?= htmlspecialchars($erro) ?>
                </p>
            <?php endif; ?>

            <form method="POST" class="login-form">

                <div class="login-grupo">
                    <label for="email">E-mail</label>
                    <input
                        class="campo"
                        type="email"
                        id="email"
                        name="email"
                        placeholder="Digite seu e-mail"
                        required
                        autocomplete="email"
                    >
                </div>

                <div class="login-grupo">
                    <label for="senha">Senha</label>
                    <input
                        class="campo"
                        type="password"
                        id="senha"
                        name="senha"
                        placeholder="Digite sua senha"
                        required
                        autocomplete="current-password"
                    >
                </div>

                <button class="botao login-botao" type="submit">
                    Entrar na minha conta
                </button>

            </form>

            <div class="login-rodape">
                <p>Ainda não possui uma conta?</p>
                <a href="cadastro.php">Cadastre-se aqui</a>
            </div>
            /*Admin*/
            <div class="login-admin">
                <span>É administrador?</span>
                <a href="../admin/login.php">Entrar como administrador</a>
            </div>

            <div class="login-assinatura">
                CUIDADO • BELEZA • BEM-ESTAR
            </div>

        </section>

    </main>

</body>
</html>
