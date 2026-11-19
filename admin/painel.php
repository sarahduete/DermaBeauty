<?php
session_start();

require_once __DIR__ . '/config/conecta.php';

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: index.php');
    exit;
}

// Total de tratamentos
$sqlTratamentos = $pdo->query(
    "SELECT COUNT(*) AS total FROM tratamentos"
);
$totalTratamentos = $sqlTratamentos->fetch()['total'];

// Total de clientes
$sqlClientes = $pdo->query(
    "SELECT COUNT(*) AS total FROM clientes"
);
$totalClientes = $sqlClientes->fetch()['total'];

// Agendamentos de hoje
// O campo data_agendamento deve estar no formato AAAA-MM-DD
// ou AAAA-MM-DD HH:MM:SS.
$sqlHoje = $pdo->query(
    "SELECT COUNT(*) AS total
     FROM agendamentos
     WHERE date(data_agendamento) = date('now', 'localtime')"
);
$totalHoje = $sqlHoje->fetch()['total'];

// Faturamento de agendamentos concluídos
$sqlFaturamento = $pdo->query(
    "SELECT COALESCE(SUM(valor), 0) AS total
     FROM agendamentos
     WHERE status = 'Concluído'"
);
$faturamento = $sqlFaturamento->fetch()['total'];

// Últimos 6 agendamentos
$sqlAgendamentos = $pdo->query(
    "SELECT a.id, c.nome AS cliente, a.valor, a.status
     FROM agendamentos a
     INNER JOIN clientes c ON c.id = a.cliente_id
     ORDER BY a.id ASC
     LIMIT 6"
);
$agendamentos = $sqlAgendamentos->fetchAll();

function escaparPainel($texto) {
    return htmlspecialchars(
        (string) $texto,
        ENT_QUOTES,
        'UTF-8'
    );
}

function dinheiroPainel($valor) {
    return 'R$ ' . number_format(
        (float) $valor,
        2,
        ',',
        '.'
    );
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel | Derma Beauty</title>
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
            <h1>Painel</h1>
        </header>

        <section class="resumo">

            <div class="resumo-item">
                <span>Tratamentos</span>
                <strong><?= (int) $totalTratamentos ?></strong>
            </div>

            <div class="resumo-item">
                <span>Clientes</span>
                <strong><?= (int) $totalClientes ?></strong>
            </div>

            <div class="resumo-item">
                <span>Agendamentos de hoje</span>
                <strong><?= (int) $totalHoje ?></strong>
            </div>

            <div class="resumo-item">
                <span>Faturamento</span>
                <strong><?= dinheiroPainel($faturamento) ?></strong>
            </div>

        </section>

        <section class="cartao">
            <h2>Últimos agendamentos</h2>

            <div class="tabela-rolagem">
                <table class="tabela">

                    <thead>
                        <tr>
                            <th>Nº</th>
                            <th>Cliente</th>
                            <th>Valor</th>
                            <th>Status</th>
                        </tr>
                    </thead>

                    <tbody>

                    <?php if (count($agendamentos) > 0): ?>

                        <?php foreach ($agendamentos as $agendamento): ?>

                            <?php
                            $classeStatus = [
                                'Pendente' => 'selo-pendente',
                                'Confirmado' => 'selo-pendente',
                                'Concluído' => 'selo-pago',
                                'Cancelado' => 'selo-cancelado'
                            ];

                            $classe = $classeStatus[$agendamento['status']]
                                ?? 'selo-pendente';
                            ?>

                            <tr>
                                <td>#<?= (int) $agendamento['id'] ?></td>

                                <td>
                                    <?= escaparPainel($agendamento['cliente']) ?>
                                </td>

                                <td>
                                    <?= dinheiroPainel($agendamento['valor']) ?>
                                </td>

                                <td>
                                    <span class="selo <?= escaparPainel($classe) ?>">
                                        <?= escaparPainel($agendamento['status']) ?>
                                    </span>
                                </td>
                            </tr>

                        <?php endforeach; ?>

                    <?php else: ?>

                        <tr>
                            <td colspan="4">
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
