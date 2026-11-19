<?php
session_start();

require_once __DIR__ . '/../admin/config/conecta.php';

$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $telefone = trim($_POST['telefone'] ?? '');
    $cpf = trim($_POST['cpf'] ?? '');
    $nascimento = trim($_POST['nascimento'] ?? '');
    $cidade = trim($_POST['cidade'] ?? '');
    $estado = trim($_POST['estado'] ?? '');
    $endereco = trim($_POST['endereco'] ?? '');
    $senha = $_POST['senha'] ?? '';

    if ($nome === '' || $email === '' || $senha === '') {
        $erro = 'Preencha nome, e-mail e senha.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erro = 'Informe um e-mail válido.';
    } elseif (strlen($senha) < 8) {
        $erro = 'A senha precisa ter pelo menos 8 caracteres.';
    } else {
        try {
            $verificar = $pdo->prepare(
                'SELECT id FROM clientes WHERE email = :email'
            );

            $verificar->execute([':email' => $email]);

            if ($verificar->fetch()) {
                $erro = 'Este e-mail já está cadastrado.';
            } else {
                $senhaHash = password_hash($senha, PASSWORD_DEFAULT);

                $sql = "INSERT INTO clientes
                    (nome, email, telefone, cpf, nascimento,
                     cidade, estado, endereco, senha)
                    VALUES
                    (:nome, :email, :telefone, :cpf, :nascimento,
                     :cidade, :estado, :endereco, :senha)";

                $stmt = $pdo->prepare($sql);

                $stmt->execute([
                    ':nome' => $nome,
                    ':email' => $email,
                    ':telefone' => $telefone,
                    ':cpf' => $cpf !== '' ? $cpf : null,
                    ':nascimento' => $nascimento,
                    ':cidade' => $cidade,
                    ':estado' => $estado,
                    ':endereco' => $endereco,
                    ':senha' => $senhaHash
                ]);

                $_SESSION['cliente_id'] = $pdo->lastInsertId();
                $_SESSION['cliente_nome'] = $nome;

                header('Location: login.php?cadastro=sucesso');
                exit;
            }

        } catch (PDOException $e) {
            $erro = 'Não foi possível realizar o cadastro. Verifique os dados informados.';
            echo($e);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Cadastro | Derma Beauty</title>

    <link rel="stylesheet" href="../css/style.css">
</head>

<body class="pagina-cadastro">

    <main class="cadastro-container">

        <section class="cadastro-card">

            <div class="cadastro-marca">
                <span class="cadastro-linha"></span>
                DERMA BEAUTY
                <span class="cadastro-linha"></span>
            </div>

            <p class="cadastro-etiqueta">ÁREA EXCLUSIVA DO CLIENTE</p>

            <h1>Crie sua conta</h1>

            <p class="cadastro-descricao">
                Faça seu cadastro e tenha seus momentos de cuidado
                sempre ao seu alcance.
            </p>

            <?php if ($erro): ?>
                <p class="cadastro-erro">
                    <?= htmlspecialchars($erro, ENT_QUOTES, 'UTF-8') ?>
                </p>
            <?php endif; ?>

            <form method="POST" class="cadastro-form">

                <div class="cadastro-grupo">
                    <label for="nome">Nome completo *</label>
                    <input
                        type="text"
                        id="nome"
                        name="nome"
                        placeholder="Seu nome completo"
                        value="<?= htmlspecialchars($_POST['nome'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                        required
                        autocomplete="name"
                    >
                </div>

                <div class="cadastro-grupo">
                    <label for="email">E-mail *</label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        placeholder="seuemail@exemplo.com"
                        value="<?= htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                        required
                        autocomplete="email"
                    >
                </div>

                <div class="cadastro-grupo">
                    <label for="telefone">Telefone</label>
                    <input
                        type="tel"
                        id="telefone"
                        name="telefone"
                        placeholder="(00) 00000-0000"
                        value="<?= htmlspecialchars($_POST['telefone'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                        autocomplete="tel"
                    >
                </div>

                <div class="cadastro-grupo">
                    <label for="cpf">CPF</label>
                    <input
                        type="text"
                        id="cpf"
                        name="cpf"
                        placeholder="000.000.000-00"
                        value="<?= htmlspecialchars($_POST['cpf'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                    >
                </div>

                <div class="cadastro-grupo">
                    <label for="nascimento">Data de nascimento</label>
                    <input
                        type="date"
                        id="nascimento"
                        name="nascimento"
                        value="<?= htmlspecialchars($_POST['nascimento'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                    >
                </div>

                <div class="cadastro-grupo">
                    <label for="cidade">Cidade</label>
                    <input
                        type="text"
                        id="cidade"
                        name="cidade"
                        placeholder="Sua cidade"
                        value="<?= htmlspecialchars($_POST['cidade'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                        autocomplete="address-level2"
                    >
                </div>

                <div class="cadastro-grupo">
                    <label for="estado">Estado</label>
                    <input
                        type="text"
                        id="estado"
                        name="estado"
                        placeholder="Ex.: SP"
                        value="<?= htmlspecialchars($_POST['estado'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                        autocomplete="address-level1"
                    >
                </div>

                <div class="cadastro-grupo">
                    <label for="endereco">Endereço</label>
                    <input
                        type="text"
                        id="endereco"
                        name="endereco"
                        placeholder="Rua, número e bairro"
                        value="<?= htmlspecialchars($_POST['endereco'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                        autocomplete="street-address"
                    >
                </div>

                <div class="cadastro-grupo cadastro-senha">
                    <label for="senha">Senha *</label>
                    <input
                        type="password"
                        id="senha"
                        name="senha"
                        placeholder="Mínimo de 8 caracteres"
                        minlength="8"
                        required
                        autocomplete="new-password"
                    >
                </div>

                <button type="submit" class="cadastro-botao">
                    Criar minha conta
                </button>

            </form>

            <div class="cadastro-rodape">
                <p>Já possui uma conta?</p>
                <a href="login.php">Entre na sua conta</a>
            </div>

            <div class="cadastro-assinatura">
                CUIDADO • BELEZA • BEM-ESTAR
            </div>

        </section>

    </main>

</body>
</html>
