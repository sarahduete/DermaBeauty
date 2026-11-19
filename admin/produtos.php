<?php
session_start();

require_once __DIR__ . '/config/conecta.php';

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: index.php');
    exit;
}

$mensagem = '';
$erro = '';

function escapar($valor) {
    return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
}

function nomeCategoria($categoria) {
    $nomes = [
        'facial' => 'Facial',
        'depilacao' => 'Depilação',
        'corporal' => 'Corporal'
    ];

    return $nomes[$categoria] ?? ucfirst($categoria);
}

function dinheiroProduto($valor) {
    return 'R$ ' . number_format((float) $valor, 2, ',', '.');
}

$categorias = ['facial', 'depilacao', 'corporal'];
$tratamentoEdicao = null;

// CADASTRAR, EDITAR OU EXCLUIR
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = $_POST['acao'] ?? '';

    // EXCLUIR TRATAMENTO
    if ($acao === 'excluir') {
        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

        if ($id && $id > 0) {
            try {
                $stmt = $pdo->prepare(
                    'DELETE FROM tratamentos WHERE id = ?'
                );
                $stmt->execute([$id]);

                if ($stmt->rowCount() > 0) {
                    header('Location: produtos.php?excluido=1');
                    exit;
                }

                $erro = 'Tratamento não encontrado.';
            } catch (PDOException $e) {
                $erro = 'Não foi possível excluir. Este tratamento pode estar vinculado a agendamentos.';
            }
        } else {
            $erro = 'Código inválido.';
        }
    }

    // CADASTRAR OU ATUALIZAR TRATAMENTO
    if ($acao === 'cadastrar' || $acao === 'atualizar') {
        $nome = trim($_POST['nome'] ?? '');
        $categoria = $_POST['categoria'] ?? '';
        $precoTexto = trim($_POST['preco'] ?? '');
        $descricao = trim($_POST['descricao'] ?? '');

        if (
            $nome === '' ||
            !in_array($categoria, $categorias, true) ||
            $precoTexto === '' ||
            !is_numeric($precoTexto) ||
            (float) $precoTexto < 0
        ) {
            $erro = 'Preencha os campos corretamente.';
        } else {
            $preco = (float) $precoTexto;

            try {
                if ($acao === 'cadastrar') {
                    $sql = 'INSERT INTO tratamentos
                            (nome, categoria, preco, descricao)
                            VALUES (?, ?, ?, ?)';

                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([
                        $nome,
                        $categoria,
                        $preco,
                        $descricao
                    ]);

                    header('Location: produtos.php?sucesso=cadastrado');
                    exit;
                }

                $id = filter_input(
                    INPUT_POST,
                    'id',
                    FILTER_VALIDATE_INT
                );

                if (!$id || $id < 1) {
                    $erro = 'Código inválido para edição.';
                } else {
                    $sql = 'UPDATE tratamentos
                            SET nome = ?, categoria = ?,
                                preco = ?, descricao = ?
                            WHERE id = ?';

                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([
                        $nome,
                        $categoria,
                        $preco,
                        $descricao,
                        $id
                    ]);

                    header('Location: produtos.php?sucesso=editado');
                    exit;
                }
            } catch (PDOException $e) {
                $erro = 'Não foi possível salvar o tratamento. Confira os dados e a estrutura da tabela.';
            }
        }
    }
}

// MENSAGENS
if (($_GET['sucesso'] ?? '') === 'cadastrado') {
    $mensagem = 'Tratamento cadastrado com sucesso!';
} elseif (($_GET['sucesso'] ?? '') === 'editado') {
    $mensagem = 'Tratamento atualizado com sucesso!';
} elseif (isset($_GET['excluido'])) {
    $mensagem = 'Tratamento excluído com sucesso!';
}

// CARREGAR TRATAMENTO PARA EDIÇÃO
if (isset($_GET['editar'])) {
    $idEditar = filter_input(
        INPUT_GET,
        'editar',
        FILTER_VALIDATE_INT
    );

    if ($idEditar && $idEditar > 0) {
        $stmt = $pdo->prepare(
            'SELECT id, nome, categoria, preco, descricao
             FROM tratamentos
             WHERE id = ?'
        );

        $stmt->execute([$idEditar]);
        $tratamentoEdicao = $stmt->fetch();

        if (!$tratamentoEdicao) {
            $erro = 'Tratamento não encontrado para edição.';
        }
    } else {
        $erro = 'Código inválido para edição.';
    }
}

// LISTAR TRATAMENTOS
$resultado = $pdo->query(
    'SELECT id, nome, categoria, preco, descricao
     FROM tratamentos
     ORDER BY id ASC'
)->fetchAll();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tratamentos | Derma Beauty</title>
    <link rel="stylesheet" href="../css/style.css">
</head>

<body>
<div class="layout">

    <aside class="menu-lateral">
        <h2 class="logo">Derma Beauty</h2>

        <nav>
            <a href="painel.php">Painel</a>
            <a href="produtos.php">Tratamentos</a>
            <a href="clientes.php">Clientes</a>
            <a href="pedidos.php">Agendamentos</a>
            <a href="index.php" class="sair">Sair</a>
        </nav>
    </aside>

    <main class="conteudo">

        <header class="cabecalho-pagina">
            <h1>Tratamentos</h1>
            <p>Cadastre e consulte os tratamentos da clínica.</p>
        </header>

        <?php if ($mensagem !== ''): ?>
            <p class="mensagem-sucesso">
                <?= escapar($mensagem) ?>
            </p>
        <?php endif; ?>

        <?php if ($erro !== ''): ?>
            <p class="mensagem-erro">
                <?= escapar($erro) ?>
            </p>
        <?php endif; ?>

        <section class="cartao">
            <h2>
                <?= $tratamentoEdicao ? 'Editar tratamento' : 'Novo tratamento' ?>
            </h2>

            <form action="produtos.php" method="post">

                <input
                    type="hidden"
                    name="acao"
                    value="<?= $tratamentoEdicao ? 'atualizar' : 'cadastrar' ?>"
                >

                <?php if ($tratamentoEdicao): ?>
                    <input
                        type="hidden"
                        name="id"
                        value="<?= (int) $tratamentoEdicao['id'] ?>"
                    >
                <?php endif; ?>

                <div class="grade-form">

                    <div class="campo">
                        <label for="nome">Nome do tratamento</label>

                        <input
                            type="text"
                            id="nome"
                            name="nome"
                            placeholder="Ex.: Limpeza de Pele"
                            value="<?= escapar($tratamentoEdicao['nome'] ?? '') ?>"
                            required
                        >
                    </div>

                    <div class="campo">
                        <label for="categoria">Categoria</label>

                        <select id="categoria" name="categoria" required>
                            <option value="">Selecione...</option>

                            <?php foreach ($categorias as $categoria): ?>
                                <option
                                    value="<?= escapar($categoria) ?>"
                                    <?= ($tratamentoEdicao['categoria'] ?? '') === $categoria ? 'selected' : '' ?>
                                >
                                    <?= escapar(nomeCategoria($categoria)) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="campo">
                        <label for="preco">Preço (R$)</label>

                        <input
                            type="number"
                            id="preco"
                            name="preco"
                            step="0.01"
                            min="0"
                            placeholder="0.00"
                            value="<?= escapar($tratamentoEdicao['preco'] ?? '') ?>"
                            required
                        >
                    </div>

                    <div class="campo campo-largo">
                        <label for="descricao">Descrição</label>

                        <textarea
                            id="descricao"
                            name="descricao"
                            placeholder="Fale um pouco sobre o tratamento"
                        ><?= escapar($tratamentoEdicao['descricao'] ?? '') ?></textarea>
                    </div>

                </div>

                <div class="acoes-form">
                    <button type="submit" class="botao">
                        <?= $tratamentoEdicao ? 'Salvar alterações' : 'Adicionar' ?>
                    </button>

                    <?php if ($tratamentoEdicao): ?>
                        <a href="produtos.php" class="botao botao-secundario">
                            Cancelar
                        </a>
                    <?php else: ?>
                        <button type="reset" class="botao botao-secundario">
                            Limpar campos
                        </button>
                    <?php endif; ?>
                </div>

            </form>
        </section>

        <section class="cartao">
            <h2>Tratamentos cadastrados</h2>

            <div class="tabela-rolagem">
                <table class="tabela">

                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Nome</th>
                            <th>Categoria</th>
                            <th>Preço</th>
                            <th>Descrição</th>
                            <th>Ações</th>
                        </tr>
                    </thead>

                    <tbody>

                    <?php if (count($resultado) > 0): ?>

                        <?php foreach ($resultado as $tratamento): ?>
                            <tr>
                                <td>
                                    <?= (int) $tratamento['id'] ?>
                                </td>

                                <td>
                                    <?= escapar($tratamento['nome']) ?>
                                </td>

                                <td>
                                    <?= escapar(nomeCategoria($tratamento['categoria'])) ?>
                                </td>

                                <td>
                                    <?= dinheiroProduto($tratamento['preco']) ?>
                                </td>

                                <td>
                                    <?= escapar($tratamento['descricao']) ?>
                                </td>

                                <td class="acoes">
                                    <a
                                        href="produtos.php?editar=<?= (int) $tratamento['id'] ?>"
                                        class="botao botao-secundario botao-pequeno"
                                    >
                                        Editar
                                    </a>

                                    <form
                                        action="produtos.php"
                                        method="post"
                                        onsubmit="return confirm('Tem certeza de que deseja excluir este tratamento?');"
                                        style="display:inline;"
                                    >
                                        <input type="hidden" name="acao" value="excluir">

                                        <input
                                            type="hidden"
                                            name="id"
                                            value="<?= (int) $tratamento['id'] ?>"
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
                                Nenhum tratamento cadastrado.
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
