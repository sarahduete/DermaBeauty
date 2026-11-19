<?php

session_start();

require_once __DIR__ . '/config/conecta.php';

$erro = '';
$email = '';
$senha = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';

    if ($email === '' || $senha === '') {

        $erro = 'Preencha o e-mail e a senha.';

    } else {

        try {

            $sql = 'SELECT id, nome, email, senha
                    FROM tbusuarios
                    WHERE email = ?';

            $stmt = $pdo->prepare($sql);
            $stmt->execute([$email]);

            $usuario = $stmt->fetch();

            if (
                $usuario &&
                password_verify($senha, $usuario['senha'])
            ) {

                session_regenerate_id(true);

                $_SESSION['usuario_id'] = $usuario['id'];
                $_SESSION['usuario_nome'] = $usuario['nome'];

                header('Location: painel.php');
                exit;

            } else {

                $erro = 'E-mail ou senha incorretos.';

            }

        } catch (PDOException $e) {

            error_log($e->getMessage());

            $erro = 'Não foi possível acessar o sistema. Tente novamente.';

        }

    }
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

<body class="pagina-login">

    <main class="login-caixa">

        <h1>Derma Beauty</h1>

        <p class="subtitulo">
            Entre com seu e-mail e senha para acessar o sistema.
        </p>

        <?php if ($erro !== ''): ?>
            <p class="mensagem-erro">
                <?= htmlspecialchars($erro, ENT_QUOTES, 'UTF-8') ?>
            </p>
        <?php endif; ?>

        <form action="index.php" method="post">

            <div class="campo">
                <label for="email">E-mail</label>

                <input
                    type="email"
                    id="email"
                    name="email"
                    placeholder="voce@exemplo.com"
                    autocomplete="username"
                    value="<?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?>"
                    required
                >
            </div>

            <div class="campo">
                <label for="senha">Senha</label>

                <input
                    type="password"
                    id="senha"
                    name="senha"
                    placeholder="Digite sua senha"
                    autocomplete="current-password"
                    required
                >
            </div>

            <button type="submit" class="botao">
                Entrar
            </button>

        </form>

        

        <p class="login-rodape">
            <a href="trocar-senha.php">Trocar minha senha</a>
        </p>



    </main>

</body>

</html>
