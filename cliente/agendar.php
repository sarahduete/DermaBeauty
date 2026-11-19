<?php

session_start();

require_once __DIR__ . '/../admin/config/conecta.php';
require_once __DIR__ . '/../funcoes/banco-horarios.php';

// Verifica se o cliente está conectado
if (!isset($_SESSION['cliente_id'])) {
    header('Location: login.php');
    exit;
}

$clienteId = (int) $_SESSION['cliente_id'];

$erro = '';
$sucesso = '';

// Proteção contra envio de formulário externo
if (empty($_SESSION['token_agendamento'])) {
    $_SESSION['token_agendamento'] = bin2hex(random_bytes(32));
}

$token = $_SESSION['token_agendamento'];

// Busca tratamentos cadastrados
$stmt = $pdo->query(
    'SELECT id, nome, preco FROM tratamentos ORDER BY nome'
);

$tratamentos = $stmt->fetchAll();

// Dados selecionados
$tratamentoId = (int) ($_GET['tratamento'] ?? $_POST['tratamento'] ?? 0);
$data = $_GET['data'] ?? $_POST['data'] ?? '';

$tratamentoSelecionado = null;

foreach ($tratamentos as $tratamento) {
    if ((int) $tratamento['id'] === $tratamentoId) {
        $tratamentoSelecionado = $tratamento;
        break;
    }
}
// Validação da data: somente hoje até 1 ano à frente
$dataValida = false;

$dataHoje = date('Y-m-d');
$dataMaxima = date('Y-m-d', strtotime('+1 year'));

if ($data !== '') {
    $dataObjeto = DateTime::createFromFormat('!Y-m-d', $data);

    $dataValida = $dataObjeto
        && $dataObjeto->format('Y-m-d') === $data
        && $data >= $dataHoje
        && $data <= $dataMaxima;
}

$horariosDisponiveis = [];

// Busca horários disponíveis
if ($tratamentoSelecionado && $dataValida) {
    try {
        $horariosDisponiveis = listarHorariosDisponiveis(
            $pdo,
            $tratamentoId,
            $data
        );
    } catch (Throwable $e) {
        $erro = 'Não foi possível consultar os horários disponíveis.';
    }
}

// Processa agendamento
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $horario = $_POST['horario'] ?? '';
    $tokenRecebido = $_POST['token'] ?? '';

    if (!hash_equals($token, $tokenRecebido)) {
        $erro = 'Solicitação inválida. Atualize a página e tente novamente.';

    } elseif (!$tratamentoSelecionado || !$dataValida) {
        $erro = 'Selecione um tratamento e uma data válida.';

    } elseif (!in_array($horario, $horariosDisponiveis, true)) {
        $erro = 'Esse horário não está mais disponível. Escolha outro.';

    } else {
        try {
            criarAgendamentoCliente(
                $pdo,
                $clienteId,
                $tratamentoId,
                $data,
                $horario
            );

            unset($_SESSION['token_agendamento']);

            header('Location: agendar.php?sucesso=1');
            exit;

        } catch (Throwable $e) {
            $erro = 'Não foi possível concluir o agendamento. Atualize a página e tente novamente.';
        }
    }
}

if (isset($_GET['sucesso'])) {
    $sucesso = 'Seu agendamento foi registrado com sucesso!';
}

?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Agendar tratamento | Derma Beauty</title>

    <link rel="stylesheet" href="../css/style.css">
</head>

<body class="pagina-agendar">

    <main class="agendar-container">

        <!-- Cabeçalho -->
        <header class="agendar-header">

            <div>
                <span class="agendar-marca">DERMA BEAUTY</span>

                <h1>Agende seu tratamento</h1>

                <p>
                    Reserve um momento especial para cuidar de você.
                </p>
            </div>

            <div class="agendar-nome-marca">
                Derma Beauty
            </div>

        </header>

        <!-- Boas-vindas -->
        <section class="agendar-boas-vindas">

            <span class="agendar-etiqueta">ÁREA EXCLUSIVA DO CLIENTE</span>

            <h2>
                Olá,
                <?= htmlspecialchars($_SESSION['cliente_nome'] ?? 'cliente', ENT_QUOTES, 'UTF-8') ?>!
            </h2>

            <p>
                Escolha seu tratamento, selecione uma data e encontre
                o horário ideal para você.
            </p>

        </section>

        <!-- Mensagens -->
        <?php if ($sucesso): ?>
            <div class="agendar-mensagem sucesso">
                <?= htmlspecialchars($sucesso, ENT_QUOTES, 'UTF-8') ?>
                <a href="meus-agendamentos.php">
                    Ver meus agendamentos
                </a>
            </div>
        <?php endif; ?>

        <?php if ($erro): ?>
            <div class="agendar-mensagem erro">
                <?= htmlspecialchars($erro, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <!-- Escolha do tratamento -->
        <section class="agendar-card">

            <div class="agendar-card-titulo">
                <span class="agendar-numero">01</span>

                <div>
                    <h2>Escolha seu tratamento</h2>
                    <p>Selecione o procedimento que deseja realizar.</p>
                </div>
            </div>

            <form method="GET" class="agendar-form">

                <div class="agendar-campo">
                    <label for="tratamento">Tratamento desejado</label>

                    <select
                        name="tratamento"
                        id="tratamento"
                        required
                    >
                        <option value="">Selecione um tratamento</option>

                        <?php foreach ($tratamentos as $item): ?>

                            <option
                                value="<?= (int) $item['id'] ?>"
                                <?= $tratamentoId === (int) $item['id'] ? 'selected' : '' ?>
                            >
                                <?= htmlspecialchars($item['nome'], ENT_QUOTES, 'UTF-8') ?>
                                - R$ <?= number_format((float) $item['preco'], 2, ',', '.') ?>
                            </option>

                        <?php endforeach; ?>

                    </select>
                </div>

                <div class="agendar-campo">
                    <label for="data">Data do atendimento</label>

                    <input
                        type="date"
                        name="data"
                        id="data"
                        min="<?= date('Y-m-d') ?>"
                        max="<?= date('Y-m-d', strtotime('+1 year')) ?>"
                        value="<?= htmlspecialchars($data, ENT_QUOTES, 'UTF-8') ?>"
                        required
                    >
                </div>

                <button type="submit" class="agendar-botao">
                    Consultar horários
                    <span>→</span>
                </button>

            </form>

        </section>

        <!-- Horários disponíveis -->
        <?php if ($tratamentoSelecionado && $dataValida): ?>

            <section class="agendar-card horarios-card">

                <div class="agendar-card-titulo">
                    <span class="agendar-numero">02</span>

                    <div>
                        <h2>Escolha seu horário</h2>
                        <p>Confira as informações do atendimento.</p>
                    </div>
                </div>

                <div class="agendar-resumo">

                    <div>
                        <span>TRATAMENTO</span>

                        <strong>
                            <?= htmlspecialchars($tratamentoSelecionado['nome'], ENT_QUOTES, 'UTF-8') ?>
                        </strong>
                    </div>

                    <div>
                        <span>DATA SELECIONADA</span>

                        <strong>
                            <?= date('d/m/Y', strtotime($data)) ?>
                        </strong>
                    </div>

                    <div>
                        <span>VALOR</span>

                        <strong>
                            R$ <?= number_format(
                                (float) $tratamentoSelecionado['preco'],
                                2,
                                ',',
                                '.'
                            ) ?>
                        </strong>
                    </div>

                </div>

                <?php if (empty($horariosDisponiveis)): ?>

                    <div class="agendar-sem-horarios">
                        <h3>Nenhum horário disponível</h3>

                        <p>
                            Não encontramos horários livres para essa data.
                            Experimente escolher outro dia.
                        </p>
                    </div>

                <?php else: ?>

                    <p class="agendar-instrucao">
                        Selecione um dos horários disponíveis:
                    </p>

                    <form method="POST" class="agendar-form-horario">

                        <input
                            type="hidden"
                            name="tratamento"
                            value="<?= $tratamentoId ?>"
                        >

                        <input
                            type="hidden"
                            name="data"
                            value="<?= htmlspecialchars($data, ENT_QUOTES, 'UTF-8') ?>"
                        >

                        <input
                            type="hidden"
                            name="token"
                            value="<?= htmlspecialchars($token, ENT_QUOTES, 'UTF-8') ?>"
                        >

                        <div class="agendar-campo">
                            <label for="horario">Horário disponível</label>

                            <select
                                name="horario"
                                id="horario"
                                required
                            >
                                <option value="">Selecione um horário</option>

                                <?php foreach ($horariosDisponiveis as $horario): ?>

                                    <option value="<?= htmlspecialchars($horario, ENT_QUOTES, 'UTF-8') ?>">
                                        <?= htmlspecialchars($horario, ENT_QUOTES, 'UTF-8') ?>
                                    </option>

                                <?php endforeach; ?>

                            </select>
                        </div>

                        <button type="submit" class="agendar-botao confirmar">
                            Confirmar agendamento
                            <span>✓</span>
                        </button>

                    </form>

                <?php endif; ?>

            </section>

        <?php endif; ?>

        <!-- Rodapé -->
        <footer class="agendar-footer">

            <a href="meus-agendamentos.php">
                ← Voltar aos meus agendamentos
            </a>

            <a href="sair.php">
                Sair da minha conta
            </a>

        </footer>

    </main>

</body>
</html>
