
<?php

session_start();

require_once __DIR__ . '/../admin/config/conecta.php';

// Verifica se o cliente está conectado
if (!isset($_SESSION['cliente_id'])) {
    header('Location: login.php');
    exit;
}

$clienteId = (int) $_SESSION['cliente_id'];

function escapar($valor) {
    return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
}

// Busca somente os agendamentos do cliente conectado
$stmt = $pdo->prepare("
    SELECT
        a.id,
        a.data_agendamento,
        a.horario,
        a.valor,
        a.status,
        t.nome AS tratamento,
        t.categoria
    FROM agendamentos a
    INNER JOIN tratamentos t
        ON t.id = a.tratamento_id
    WHERE a.cliente_id = ?
    ORDER BY a.data_agendamento DESC, a.horario DESC
");

$stmt->execute([$clienteId]);
$agendamentos = $stmt->fetchAll();

// Classes visuais dos status
$classesStatus = [
    'Pendente' => 'status-pendente',
    'Confirmado' => 'status-confirmado',
    'Concluído' => 'status-concluido',
    'Cancelado' => 'status-cancelado'
];

?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Meus Agendamentos | Derma Beauty</title>

    <link rel="stylesheet" href="../css/style.css">
</head>

<body class="pagina-agendamentos">

    <main class="agendamentos-container">

        <!-- Cabeçalho -->
        <header class="agendamentos-header">

            <div class="header-conteudo">
                <span class="header-etiqueta">DERMA BEAUTY</span>

                <h1>Meus Agendamentos</h1>

                <p>
                    Seu momento de cuidado começa aqui.
                    Acompanhe seus tratamentos e consultas.
                </p>
            </div>


        </header>

        <!-- Boas-vindas -->
        <section class="boas-vindas">

            <div class="boas-vindas-texto">
                <span class="boas-vindas-etiqueta">ÁREA DO CLIENTE</span>

                <h2>
                    Olá, <?= escapar($_SESSION['cliente_nome'] ?? 'cliente') ?>!
                </h2>

                <p>
                    Que bom ter você aqui. Confira seus horários e
                    continue reservando um tempo para você.
                </p>
            </div>

            <a href="agendar.php" class="botao-agendar">
                <span>+</span>
                Agendar tratamento
            </a>

        </section>

        <!-- Histórico -->
        <section class="historico-card">

            <div class="historico-topo">

                <div>
                    <span class="historico-etiqueta">SEU CUIDADO</span>
                    <h2>Histórico de agendamentos</h2>
                    <p>Consulte os detalhes dos seus atendimentos.</p>
                </div>

                <div class="contador-agendamentos">
                    <strong><?= count($agendamentos) ?></strong>
                    <span>
                        <?= count($agendamentos) === 1
                            ? 'agendamento'
                            : 'agendamentos' ?>
                    </span>
                </div>

            </div>

            <?php if (count($agendamentos) > 0): ?>

                <div class="tabela-rolagem">

                    <table class="tabela-agendamentos">

                        <thead>
                            <tr>
                                <th>Tratamento</th>
                                <th>Categoria</th>
                                <th>Data</th>
                                <th>Horário</th>
                                <th>Valor</th>
                                <th>Status</th>
                            </tr>
                        </thead>

                        <tbody>

                            <?php foreach ($agendamentos as $agendamento): ?>

                                <?php

                                $timestamp = strtotime(
                                    $agendamento['data_agendamento']
                                );

                                $dataExibicao = $timestamp !== false
                                    ? date('d/m/Y', $timestamp)
                                    : $agendamento['data_agendamento'];

                                $classe = $classesStatus[$agendamento['status']]
                                    ?? 'status-pendente';

                                ?>

                                <tr>

                                    <td class="nome-tratamento">
                                        <?= escapar($agendamento['tratamento']) ?>
                                    </td>

                                    <td>
                                        <?= escapar($agendamento['categoria']) ?>
                                    </td>

                                    <td>
                                        <?= escapar($dataExibicao) ?>
                                    </td>

                                    <td>
                                        <?= escapar($agendamento['horario']) ?>
                                    </td>

                                    <td class="valor-tratamento">
                                        R$ <?= number_format(
                                            (float) $agendamento['valor'],
                                            2,
                                            ',',
                                            '.'
                                        ) ?>
                                    </td>

                                    <td>
                                        <span class="status-agendamento <?= escapar($classe) ?>">
                                            <?= escapar($agendamento['status']) ?>
                                        </span>
                                    </td>

                                </tr>

                            <?php endforeach; ?>

                        </tbody>

                    </table>

                </div>

            <?php else: ?>

                <div class="sem-agendamentos">

                    <div class="sem-agendamentos-icone">
                        ♡
                    </div>

                    <h3>Nenhum agendamento por enquanto</h3>

                    <p>
                        Você ainda não possui tratamentos agendados.
                        Que tal reservar seu primeiro momento de cuidado?
                    </p>

                    <a href="agendar.php" class="botao-agendar">
                        Agendar meu primeiro tratamento
                    </a>

                </div>

            <?php endif; ?>

        </section>

        <!-- Rodapé -->
        <footer class="agendamentos-footer">

            <span>DERMA BEAUTY · CUIDADO E BEM-ESTAR</span>

            <a href="sair.php">Sair da minha conta</a>

        </footer>

    </main>

</body>
</html>
