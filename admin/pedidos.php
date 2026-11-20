<?php

session_start();

require_once __DIR__ . '/config/conecta.php';
require_once __DIR__ . '/../funcoes/banco-horarios.php';

// Proteção da página
if (!isset($_SESSION['usuario_id'])) {
    header('Location: index.php');
    exit;
}

// Funções auxiliares
function escaparPedido($valor) {
    return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
}

function dinheiroPedido($valor) {
    return 'R$ ' . number_format((float) $valor, 2, ',', '.');
}

$mensagem = '';
$erro = '';

$statusPermitidos = [
    'Pendente',
    'Confirmado',
    'Concluído',
    'Cancelado'
];

// ========================================
// CADASTRAR AGENDAMENTO
// ========================================

if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    ($_POST['acao'] ?? '') === 'cadastrar'
) {

    $clienteId = filter_input(
        INPUT_POST,
        'cliente',
        FILTER_VALIDATE_INT
    );

    $tratamentoId = filter_input(
        INPUT_POST,
        'tratamento',
        FILTER_VALIDATE_INT
    );

    $data = trim($_POST['data_agendamento'] ?? '');
    $horario = trim($_POST['horario'] ?? '');
    $status = $_POST['status'] ?? 'Pendente';

    $dataObjeto = DateTime::createFromFormat('!Y-m-d', $data);

    $horarioValido = preg_match(
        '/^(?:[01]\d|2[0-3]):[0-5]\d$/',
        $horario
    );

    if (
        !$clienteId ||
        $clienteId < 1 ||
        !$tratamentoId ||
        $tratamentoId < 1 ||
        !$dataObjeto ||
        $dataObjeto->format('Y-m-d') !== $data ||
        !$horarioValido ||
        !in_array($status, $statusPermitidos, true)
    ) {

        $erro = 'Preencha todos os campos corretamente.';

    } else {

        try {

            // Busca o tratamento e seu preço
            $stmt = $pdo->prepare(
                'SELECT id, preco FROM tratamentos WHERE id = ?'
            );

            $stmt->execute([$tratamentoId]);

            $tratamento = $stmt->fetch();

            // Verifica se o cliente existe
            $stmt = $pdo->prepare(
                'SELECT id FROM clientes WHERE id = ?'
            );

            $stmt->execute([$clienteId]);

            $cliente = $stmt->fetch();

            if (!$tratamento || !$cliente) {

                $erro = 'Selecione um cliente e um tratamento válidos.';

            } else {

                // Confere a disponibilidade do horário
                $horariosDisponiveis = listarHorariosDisponiveis(
                    $pdo,
                    $tratamentoId,
                    $data
                );

                if (!in_array($horario, $horariosDisponiveis, true)) {

                    $erro = 'Esse horário não está disponível. Escolha outro.';

                } else {

                    $preco = (float) $tratamento['preco'];

                    // Registra o agendamento
                    $stmt = $pdo->prepare(
                        'INSERT INTO agendamentos
                        (
                            cliente_id,
                            tratamento_id,
                            data_agendamento,
                            horario,
                            status,
                            valor
                        )
                        VALUES (?, ?, ?, ?, ?, ?)'
                    );

                    $stmt->execute([
                        $clienteId,
                        $tratamentoId,
                        $data,
                        $horario,
                        $status,
                        $preco
                    ]);

                    header('Location: pedidos.php?sucesso=1');
                    exit;
                }
            }

        } catch (Throwable $e) {

            $erro = 'Não foi possível registrar o agendamento. Verifique os dados e tente novamente.';

        }
    }
}

// ========================================
// ATUALIZAR STATUS
// ========================================

if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    ($_POST['acao'] ?? '') === 'atualizar_status'
) {

    $id = filter_input(
        INPUT_POST,
        'id',
        FILTER_VALIDATE_INT
    );

    $novoStatus = $_POST['status'] ?? '';

    if (
        $id &&
        $id > 0 &&
        in_array($novoStatus, $statusPermitidos, true)
    ) {

        try {

            $stmt = $pdo->prepare(
                'UPDATE agendamentos
                 SET status = ?
                 WHERE id = ?'
            );

            $stmt->execute([
                $novoStatus,
                $id
            ]);

            header('Location: pedidos.php?atualizado=1');
            exit;

        } catch (PDOException $e) {

            $erro = 'Não foi possível atualizar o status.';
        }

    } else {

        $erro = 'Dados inválidos para atualizar o agendamento.';
    }
}

// ========================================
// CANCELAR AGENDAMENTO
// ========================================

if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    ($_POST['acao'] ?? '') === 'cancelar'
) {

    $id = filter_input(
        INPUT_POST,
        'id',
        FILTER_VALIDATE_INT
    );

    if ($id && $id > 0) {

        try {

            $stmt = $pdo->prepare(
                "UPDATE agendamentos
                 SET status = 'Cancelado'
                 WHERE id = ?"
            );

            $stmt->execute([$id]);

            header('Location: pedidos.php?cancelado=1');
            exit;

        } catch (PDOException $e) {

            $erro = 'Não foi possível cancelar o agendamento.';
        }

    } else {

        $erro = 'Agendamento inválido.';
    }
}

// ========================================
// MENSAGENS
// ========================================

if (isset($_GET['sucesso'])) {
    $mensagem = 'Agendamento registrado com sucesso!';
}

if (isset($_GET['atualizado'])) {
    $mensagem = 'Status atualizado com sucesso!';
}

if (isset($_GET['cancelado'])) {
    $mensagem = 'Agendamento cancelado com sucesso!';
}

// ========================================
// BUSCAR CLIENTES
// ========================================

$clientes = $pdo->query(
    'SELECT id, nome FROM clientes ORDER BY nome'
)->fetchAll();

// ========================================
// BUSCAR TRATAMENTOS
// ========================================

$tratamentos = $pdo->query(
    'SELECT id, nome, preco FROM tratamentos ORDER BY nome'
)->fetchAll();

// ========================================
// BUSCAR AGENDAMENTOS
// ========================================

$sql = "
    SELECT
        a.id,
        c.nome AS cliente,
        t.nome AS tratamento,
        a.data_agendamento,
        a.horario,
        a.valor,
        a.status
    FROM agendamentos a
    INNER JOIN clientes c ON c.id = a.cliente_id
    INNER JOIN tratamentos t ON t.id = a.tratamento_id
    ORDER BY a.data_agendamento ASC, a.horario ASC
";

$agendamentos = $pdo->query($sql)->fetchAll();

// ========================================
// CLASSES DOS STATUS
// ========================================

$classesStatus = [
    'Pendente' => 'selo-pendente',
    'Confirmado' => 'selo-confirmado',
    'Concluído' => 'selo-pago',
    'Cancelado' => 'selo-cancelado'
];

?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Agendamentos | Derma Beauty</title>

    <link rel="stylesheet" href="../css/style.css">

</head>

<body>

<div class="layout">

    <?php require __DIR__ . '/menu.php'; ?>

    <!-- CONTEÚDO PRINCIPAL -->

    <main class="conteudo">

        <header class="cabecalho-pagina">

            <h1>Agendamentos</h1>

            <p>
                Gerencie os atendimentos da Derma Beauty.
            </p>

        </header>

        <!-- MENSAGENS -->

        <?php if ($mensagem !== ''): ?>

            <p class="mensagem-sucesso">
                <?= escaparPedido($mensagem) ?>
            </p>

        <?php endif; ?>

        <?php if ($erro !== ''): ?>

            <p class="mensagem-erro">
                <?= escaparPedido($erro) ?>
            </p>

        <?php endif; ?>

        <!-- NOVO AGENDAMENTO -->

        <section class="cartao">

            <h2>Novo agendamento</h2>

            <p>
                Registre um atendimento manualmente.
            </p>

            <form action="pedidos.php" method="POST">

                <input
                    type="hidden"
                    name="acao"
                    value="cadastrar"
                >

                <div class="grade-form">

                    <!-- CLIENTE -->

                    <div class="campo">

                        <label for="cliente">
                            Cliente
                        </label>

                        <select
                            id="cliente"
                            name="cliente"
                            required
                        >

                            <option value="">
                                Selecione um cliente
                            </option>

                            <?php foreach ($clientes as $cliente): ?>

                                <option value="<?= (int) $cliente['id'] ?>">

                                    <?= escaparPedido($cliente['nome']) ?>

                                </option>

                            <?php endforeach; ?>

                        </select>

                    </div>

                    <!-- TRATAMENTO -->

                    <div class="campo">

                        <label for="tratamento">
                            Tratamento
                        </label>

                        <select
                            id="tratamento"
                            name="tratamento"
                            required
                        >

                            <option value="">
                                Selecione um tratamento
                            </option>

                            <?php foreach ($tratamentos as $tratamento): ?>

                                <option value="<?= (int) $tratamento['id'] ?>">

                                    <?= escaparPedido($tratamento['nome']) ?>

                                    —
                                    <?= dinheiroPedido($tratamento['preco']) ?>

                                </option>

                            <?php endforeach; ?>

                        </select>

                    </div>

                    <!-- DATA -->

                    <div class="campo">

                        <label for="data_agendamento">
                            Data do atendimento
                        </label>

                        <input
                            type="date"
                            id="data_agendamento"
                            name="data_agendamento"
                            min="<?= date('Y-m-d') ?>"
                            required
                        >

                    </div>

                    <!-- HORÁRIO -->

                    <div class="campo">

                        <label for="horario">
                            Horário
                        </label>

                        <input
                            type="time"
                            id="horario"
                            name="horario"
                            step="1800"
                            required
                        >

                    </div>

                    <!-- STATUS -->

                    <div class="campo">

                        <label for="status">
                            Status
                        </label>

                        <select
                            id="status"
                            name="status"
                            required
                        >

                            <option value="Pendente">
                                Pendente
                            </option>

                            <option value="Confirmado">
                                Confirmado
                            </option>

                            <option value="Concluído">
                                Concluído
                            </option>

                            <option value="Cancelado">
                                Cancelado
                            </option>

                        </select>

                    </div>

                </div>

                <div class="acoes-form">

                    <button type="submit" class="botao">

                        Registrar agendamento

                    </button>

                    <button
                        type="reset"
                        class="botao botao-secundario"
                    >

                        Limpar

                    </button>

                </div>

            </form>

        </section>

        <!-- LISTAGEM DE AGENDAMENTOS -->

        <section class="cartao">

            <h2>Agendamentos registrados</h2>

            <p>
                Confira os atendimentos e atualize seus status.
            </p>

            <div class="tabela-rolagem">

                <table class="tabela">

                    <thead>

                        <tr>

                            <th>Nº</th>

                            <th>Cliente</th>

                            <th>Tratamento</th>

                            <th>Data</th>

                            <th>Horário</th>

                            <th>Valor</th>

                            <th>Status</th>

                            <th>Ações</th>

                        </tr>

                    </thead>

                    <tbody>

                        <?php if (count($agendamentos) > 0): ?>

                            <?php foreach ($agendamentos as $agendamento): ?>

                                <?php

                                $classe = $classesStatus[$agendamento['status']]
                                    ?? 'selo-pendente';

                                $timestamp = strtotime(
                                    $agendamento['data_agendamento']
                                );

                                $timestamp = strtotime($agendamento['data_agendamento']);

                                $dataExibicao = $timestamp !== false
                                    ? date('d/m/Y', $timestamp)
                                    : $agendamento['data_agendamento'];

                                $dataExibicao .= ' às ' . $agendamento['horario'];
                                ?>

                                <tr>

                                    <td>
                                        #<?= (int) $agendamento['id'] ?>
                                    </td>

                                    <td>
                                        <?= escaparPedido($agendamento['cliente']) ?>
                                    </td>

                                    <td>
                                        <?= escaparPedido($agendamento['tratamento']) ?>
                                    </td>

                                    <td>
                                        <?= escaparPedido($dataExibicao) ?>
                                    </td>

                                    <td>
                                        <?= escaparPedido($agendamento['horario']) ?>
                                    </td>

                                    <td>
                                        <?= dinheiroPedido($agendamento['valor']) ?>
                                    </td>

                                    <td>

                                        <span class="selo <?= escaparPedido($classe) ?>">

                                            <?= escaparPedido($agendamento['status']) ?>

                                        </span>

                                    </td>

                                    <td class="acoes">

                                        <!-- ATUALIZAR STATUS -->

                                        <form
                                            method="POST"
                                            action="pedidos.php"
                                        >

                                            <input
                                                type="hidden"
                                                name="acao"
                                                value="atualizar_status"
                                            >

                                            <input
                                                type="hidden"
                                                name="id"
                                                value="<?= (int) $agendamento['id'] ?>"
                                            >

                                            <select
                                                name="status"
                                                required
                                            >

                                                <?php foreach ($statusPermitidos as $status): ?>

                                                    <option
                                                        value="<?= escaparPedido($status) ?>"
                                                        <?= $agendamento['status'] === $status ? 'selected' : '' ?>
                                                    >

                                                        <?= escaparPedido($status) ?>

                                                    </option>

                                                <?php endforeach; ?>

                                            </select>

                                            <button
                                                type="submit"
                                                class="botao botao-pequeno"
                                            >

                                                Atualizar

                                            </button>

                                        </form>

                                        <!-- CANCELAR -->

                                        <?php if ($agendamento['status'] !== 'Cancelado'): ?>

                                            <form
                                                method="POST"
                                                action="pedidos.php"
                                            >

                                                <input
                                                    type="hidden"
                                                    name="acao"
                                                    value="cancelar"
                                                >

                                                <input
                                                    type="hidden"
                                                    name="id"
                                                    value="<?= (int) $agendamento['id'] ?>"
                                                >

                                                <button
                                                    type="submit"
                                                    class="botao botao-perigo botao-pequeno"
                                                >

                                                    Cancelar

                                                </button>

                                            </form>

                                        <?php endif; ?>

                                    </td>

                                </tr>

                            <?php endforeach; ?>

                        <?php else: ?>

                            <tr>

                                <td colspan="8">

                                    Nenhum agendamento cadastrado.

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
