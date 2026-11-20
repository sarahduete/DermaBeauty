<?php

session_start();

require_once __DIR__ . '/config/conecta.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: index.php');
    exit;
}

$mensagem = '';
$erro = '';
$clienteEdicao = null;

// Escapar textos para exibição no HTML
function escaparCliente($valor) {
    return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
}

// Excluir cliente
if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    ($_POST['acao'] ?? '') === 'excluir'
) {
    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

    if ($id && $id > 0) {
        try {
            $stmt = $pdo->prepare(
                'DELETE FROM clientes WHERE id = ?'
            );

            $stmt->execute([$id]);

            if ($stmt->rowCount() > 0) {
                header('Location: clientes.php?excluido=1');
                exit;
            }

            header('Location: clientes.php?erro=nao_encontrado');
            exit;

        } catch (PDOException $e) {
            $erro = 'Este cliente possui agendamentos vinculados ou não pode ser excluído.';
        }
    } else {
        $erro = 'Código de cliente inválido.';
    }
}

// Cadastrar ou atualizar cliente
if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    in_array(
        $_POST['acao'] ?? '',
        ['cadastrar', 'atualizar'],
        true
    )
) {
    $acao = $_POST['acao'];

    $nome = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $telefone = trim($_POST['telefone'] ?? '');
    $cpf = trim($_POST['cpf'] ?? '');
    $nascimento = trim($_POST['nascimento'] ?? '');
    $cidade = trim($_POST['cidade'] ?? '');
    $estado = trim($_POST['estado'] ?? '');
    $endereco = trim($_POST['endereco'] ?? '');

    if (
        $nome === '' ||
        !filter_var($email, FILTER_VALIDATE_EMAIL) ||
        $cpf === ''
    ) {
        $erro = 'Preencha o nome, um e-mail válido e o CPF.';
    } else {
        $nascimentoBanco = $nascimento !== ''
            ? $nascimento
            : null;

        try {
            if ($acao === 'cadastrar') {
                $sql = 'INSERT INTO clientes
                    (
                        nome,
                        email,
                        telefone,
                        cpf,
                        nascimento,
                        cidade,
                        estado,
                        endereco
                    )
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)';

                $stmt = $pdo->prepare($sql);

                $stmt->execute([
                    $nome,
                    $email,
                    $telefone,
                    $cpf,
                    $nascimentoBanco,
                    $cidade,
                    $estado,
                    $endereco
                ]);

                header('Location: clientes.php?sucesso=cadastrado');
                exit;

            } else {
                $id = filter_input(
                    INPUT_POST,
                    'id',
                    FILTER_VALIDATE_INT
                );

                if (!$id || $id < 1) {
                    $erro = 'Código inválido para edição.';
                } else {
                    $sql = 'UPDATE clientes
                        SET
                            nome = ?,
                            email = ?,
                            telefone = ?,
                            cpf = ?,
                            nascimento = ?,
                            cidade = ?,
                            estado = ?,
                            endereco = ?
                        WHERE id = ?';

                    $stmt = $pdo->prepare($sql);

                    $stmt->execute([
                        $nome,
                        $email,
                        $telefone,
                        $cpf,
                        $nascimentoBanco,
                        $cidade,
                        $estado,
                        $endereco,
                        $id
                    ]);

                    header('Location: clientes.php?sucesso=editado');
                    exit;
                }
            }

        } catch (PDOException $e) {
            if (
                str_contains(
                    $e->getMessage(),
                    'UNIQUE constraint failed'
                )
            ) {
                $erro = 'Já existe um cliente com esse CPF ou e-mail.';
            } else {
                $erro = 'Não foi possível salvar o cliente. Verifique os dados e as colunas da tabela.';
            }
        }
    }
}

// Mensagens de retorno
if (($_GET['sucesso'] ?? '') === 'cadastrado') {
    $mensagem = 'Cliente cadastrado com sucesso!';

} elseif (($_GET['sucesso'] ?? '') === 'editado') {
    $mensagem = 'Cliente atualizado com sucesso!';

} elseif (isset($_GET['excluido'])) {
    $mensagem = 'Cliente excluído com sucesso!';

} elseif (($_GET['erro'] ?? '') === 'nao_encontrado') {
    $erro = 'Cliente não encontrado.';
}

// Carregar cliente para edição
if (isset($_GET['editar'])) {
    $idEditar = filter_input(
        INPUT_GET,
        'editar',
        FILTER_VALIDATE_INT
    );

    if ($idEditar && $idEditar > 0) {
        $stmt = $pdo->prepare(
            'SELECT
                id,
                nome,
                email,
                telefone,
                cpf,
                nascimento,
                cidade,
                estado,
                endereco
             FROM clientes
             WHERE id = ?'
        );

        $stmt->execute([$idEditar]);

        $clienteEdicao = $stmt->fetch();

        if (!$clienteEdicao) {
            $erro = 'Cliente não encontrado para edição.';
        }
    }
}

// Listar clientes
$stmt = $pdo->query(
    'SELECT
        id,
        nome,
        email,
        telefone,
        cpf,
        nascimento,
        cidade,
        estado,
        endereco
     FROM clientes
     ORDER BY id ASC'
);

$resultado = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Clientes | Derma Beauty</title>

    <link rel="stylesheet" href="../css/style.css">
</head>

<body>

<div class="layout">

    <?php require __DIR__ . '/menu.php'; ?>

    <main class="conteudo">

        <header class="cabecalho-pagina">
            <h1>Clientes</h1>
            <p>Cadastre e consulte os clientes da clínica.</p>
        </header>

        <?php if ($mensagem !== ''): ?>
            <p class="mensagem-sucesso">
                <?= escaparCliente($mensagem) ?>
            </p>
        <?php endif; ?>

        <?php if ($erro !== ''): ?>
            <p class="mensagem-erro">
                <?= escaparCliente($erro) ?>
            </p>
        <?php endif; ?>

        <section class="cartao">

            <h2>
                <?= $clienteEdicao ? 'Editar cliente' : 'Novo cliente' ?>
            </h2>

            <form
                action="clientes.php<?= $clienteEdicao ? '?editar=' . (int) $clienteEdicao['id'] : '' ?>"
                method="post"
            >

                <input
                    type="hidden"
                    name="acao"
                    value="<?= $clienteEdicao ? 'atualizar' : 'cadastrar' ?>"
                >

                <?php if ($clienteEdicao): ?>
                    <input
                        type="hidden"
                        name="id"
                        value="<?= (int) $clienteEdicao['id'] ?>"
                    >
                <?php endif; ?>

                <div class="grade-form">

                    <div class="campo campo-largo">
                        <label for="nome">Nome completo</label>

                        <input
                            type="text"
                            id="nome"
                            name="nome"
                            placeholder="Ex.: Maria Souza"
                            value="<?= escaparCliente($clienteEdicao['nome'] ?? '') ?>"
                            required
                        >
                    </div>

                    <div class="campo">
                        <label for="email">E-mail</label>

                        <input
                            type="email"
                            id="email"
                            name="email"
                            placeholder="maria@exemplo.com"
                            value="<?= escaparCliente($clienteEdicao['email'] ?? '') ?>"
                            required
                        >
                    </div>

                    <div class="campo">
                        <label for="telefone">Telefone</label>

                        <input
                            type="tel"
                            id="telefone"
                            name="telefone"
                            placeholder="(11) 99999-9999"
                            value="<?= escaparCliente($clienteEdicao['telefone'] ?? '') ?>"
                        >
                    </div>

                    <div class="campo">
                        <label for="cpf">CPF</label>

                        <input
                            type="text"
                            id="cpf"
                            name="cpf"
                            placeholder="000.000.000-00"
                            value="<?= escaparCliente($clienteEdicao['cpf'] ?? '') ?>"
                            required
                        >
                    </div>

                    <div class="campo">
                        <label for="nascimento">Data de nascimento</label>

                        <input
                            type="date"
                            id="nascimento"
                            name="nascimento"
                            value="<?= escaparCliente($clienteEdicao['nascimento'] ?? '') ?>"
                        >
                    </div>

                    <div class="campo">
                        <label for="cidade">Cidade</label>

                        <input
                            type="text"
                            id="cidade"
                            name="cidade"
                            placeholder="Ex.: São Paulo"
                            value="<?= escaparCliente($clienteEdicao['cidade'] ?? '') ?>"
                        >
                    </div>

                    <div class="campo">
                        <label for="estado">Estado</label>

                        <select id="estado" name="estado">

                            <option value="">Selecione...</option>

                            <?php
                            $estados = [
                                'AC' => 'Acre',
                                'AL' => 'Alagoas',
                                'AP' => 'Amapá',
                                'AM' => 'Amazonas',
                                'BA' => 'Bahia',
                                'CE' => 'Ceará',
                                'DF' => 'Distrito Federal',
                                'ES' => 'Espírito Santo',
                                'GO' => 'Goiás',
                                'MA' => 'Maranhão',
                                'MT' => 'Mato Grosso',
                                'MS' => 'Mato Grosso do Sul',
                                'MG' => 'Minas Gerais',
                                'PA' => 'Pará',
                                'PB' => 'Paraíba',
                                'PR' => 'Paraná',
                                'PE' => 'Pernambuco',
                                'PI' => 'Piauí',
                                'RJ' => 'Rio de Janeiro',
                                'RN' => 'Rio Grande do Norte',
                                'RS' => 'Rio Grande do Sul',
                                'RO' => 'Rondônia',
                                'RR' => 'Roraima',
                                'SC' => 'Santa Catarina',
                                'SP' => 'São Paulo',
                                'SE' => 'Sergipe',
                                'TO' => 'Tocantins'
                            ];

                            foreach ($estados as $sigla => $nomeEstado):
                            ?>

                                <option
                                    value="<?= escaparCliente($sigla) ?>"
                                    <?= ($clienteEdicao['estado'] ?? '') === $sigla ? 'selected' : '' ?>
                                >
                                    <?= escaparCliente($nomeEstado) ?>
                                </option>

                            <?php endforeach; ?>

                        </select>
                    </div>

                    <div class="campo campo-largo">
                        <label for="endereco">Endereço</label>

                        <input
                            type="text"
                            id="endereco"
                            name="endereco"
                            placeholder="Rua, número e bairro"
                            value="<?= escaparCliente($clienteEdicao['endereco'] ?? '') ?>"
                        >
                    </div>

                </div>

                <div class="acoes-form">

                    <button type="submit" class="botao">
                        <?= $clienteEdicao ? 'Salvar alterações' : 'Salvar cliente' ?>
                    </button>

                    <?php if ($clienteEdicao): ?>

                        <a
                            href="clientes.php"
                            class="botao botao-secundario"
                        >
                            Cancelar
                        </a>

                    <?php else: ?>

                        <button
                            type="reset"
                            class="botao botao-secundario"
                        >
                            Limpar
                        </button>

                    <?php endif; ?>

                </div>

            </form>

        </section>

        <section class="cartao">

            <h2>Clientes cadastrados</h2>

            <div class="tabela-rolagem">

                <table class="tabela">

                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Nome</th>
                            <th>E-mail</th>
                            <th>Telefone</th>
                            <th>Cidade</th>
                            <th>Ações</th>
                        </tr>
                    </thead>

                    <tbody>

                        <?php if (count($resultado) > 0): ?>

                            <?php foreach ($resultado as $cliente): ?>

                                <tr>
                                    <td>
                                        <?= (int) $cliente['id'] ?>
                                    </td>

                                    <td>
                                        <?= escaparCliente($cliente['nome']) ?>
                                    </td>

                                    <td>
                                        <?= escaparCliente($cliente['email']) ?>
                                    </td>

                                    <td>
                                        <?= escaparCliente($cliente['telefone']) ?>
                                    </td>

                                    <td>
                                        <?= escaparCliente($cliente['cidade']) ?>
                                    </td>

                                    <td class="acoes">

                                        <a
                                            href="clientes.php?editar=<?= (int) $cliente['id'] ?>"
                                            class="botao botao-secundario botao-pequeno"
                                        >
                                            Editar
                                        </a>

                                        <form
                                            action="clientes.php"
                                            method="post"
                                            onsubmit="return confirm('Tem certeza de que deseja excluir este cliente?');"
                                            style="display:inline;"
                                        >

                                            <input
                                                type="hidden"
                                                name="acao"
                                                value="excluir"
                                            >

                                            <input
                                                type="hidden"
                                                name="id"
                                                value="<?= (int) $cliente['id'] ?>"
                                            >

                                            <button
                                                type="submit"
                                                class="botao botao-perigo botao-pequeno"
                                            >
                                                Excluir
                                            </button>

                                        </form>

                                    </td>
                                </tr>

                            <?php endforeach; ?>

                        <?php else: ?>

                            <tr>
                                <td colspan="6">
                                    Nenhum cliente cadastrado.
                                </td>
                            </tr>

                        <?php endif; ?>

                    </tbody>

                </table>

            </div>

        </section>

    </main>

</div>

</body>
</html>
