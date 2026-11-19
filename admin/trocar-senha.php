
<?php

session_start();

require_once __DIR__ . '/config/conecta.php';

$mensagem = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $senhaAtual = $_POST['senha_atual'] ?? '';
    $novaSenha = $_POST['nova_senha'] ?? '';
    $confirmarSenha = $_POST['confirmar_senha'] ?? '';

    if ($senhaAtual === '' || $novaSenha === '' || $confirmarSenha === '') {

        $mensagem = 'Preencha todos os campos.';

    } elseif (strlen($novaSenha) < 8) {

        $mensagem = 'A nova senha deve ter pelo menos 8 caracteres.';

    } elseif ($novaSenha !== $confirmarSenha) {

        $mensagem = 'As senhas novas não coincidem.';

    } else {

        $stmt = $pdo->prepare(
            'SELECT id, senha FROM tbusuarios WHERE id = ?'
        );

        $stmt->execute([$_SESSION['usuario_id'] ?? 0]);
        $usuario = $stmt->fetch();

        if (!$usuario || !password_verify($senhaAtual, $usuario['senha'])) {

            $mensagem = 'A senha atual está incorreta.';

        } else {

            $hash = password_hash($novaSenha, PASSWORD_DEFAULT);

            $stmt = $pdo->prepare(
                'UPDATE tbusuarios SET senha = ? WHERE id = ?'
            );

            $stmt->execute([$hash, $usuario['id']]);

            $mensagem = 'Senha alterada com sucesso!';

        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trocar senha | Derma Beauty</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body class="pagina-login">

    <main class="login-caixa">
        <h1>Trocar senha</h1>

        <?php if ($mensagem !== ''): ?>
            <p class="mensagem-erro">
                <?= htmlspecialchars($mensagem, ENT_QUOTES, 'UTF-8') ?>
            </p>
        <?php endif; ?>

        <form method="post">
            <div class="campo">
                <label for="senha_atual">Senha atual</label>
                <input type="password" id="senha_atual"
                       name="senha_atual" required>
            </div>

            <div class="campo">
                <label for="nova_senha">Nova senha</label>
                <input type="password" id="nova_senha"
                       name="nova_senha" minlength="8" required>
            </div>

            <div class="campo">
                <label for="confirmar_senha">Confirmar nova senha</label>
                <input type="password" id="confirmar_senha"
                       name="confirmar_senha" minlength="8" required>
            </div>

            <button type="submit" class="botao">
                Alterar senha
            </button>
        </form>

        <p class="login-rodape">
            <a href="painel.php">Voltar ao painel</a>
        </p>
    </main>

</body>
</html>
