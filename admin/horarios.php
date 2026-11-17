
<?php

session_start();

require_once __DIR__ . '/config/conecta.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: index.php');
    exit;
}

$dias = [
    1 => 'Segunda-feira',
    2 => 'Terça-feira',
    3 => 'Quarta-feira',
    4 => 'Quinta-feira',
    5 => 'Sexta-feira',
    6 => 'Sábado',
    7 => 'Domingo'
];

$mensagem = '';
$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    try {

        $pdo->beginTransaction();

        $salvar = $pdo->prepare("
            UPDATE horarios_funcionamento

            SET
                hora_inicio = ?,
                hora_fim = ?,
                intervalo_minutos = ?,
                ativo = ?

            WHERE dia_semana = ?
        ");

        foreach ($dias as $numero => $nome) {

            $ativo = isset($_POST['ativo'][$numero]) ? 1 : 0;

            $inicio = $_POST['hora_inicio'][$numero] ?? '';
            $fim = $_POST['hora_fim'][$numero] ?? '';

            $intervalo = (int) (
                $_POST['intervalo'][$numero] ?? 30
            );

            if (
                !preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $inicio) ||
                !preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $fim)
            ) {
                throw new RuntimeException('Horário inválido.');
            }

            if ($inicio >= $fim) {
                throw new RuntimeException(
                    'O encerramento deve ser posterior à abertura.'
                );
            }

            if (!in_array($intervalo, [15, 30, 60], true)) {
                throw new RuntimeException('Intervalo inválido.');
            }

            $salvar->execute([
                $inicio,
                $fim,
                $intervalo,
                $ativo,
                $numero
            ]);
        }

        $pdo->commit();

        $mensagem = 'Horários atualizados com sucesso!';

    } catch (Throwable $e) {

        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        $erro = $e instanceof RuntimeException
            ? $e->getMessage()
            : 'Não foi possível salvar os horários.';

        error_log($e->getMessage());
    }
}

$consulta = $pdo->query("
    SELECT *
    FROM horarios_funcionamento
    ORDER BY dia_semana
");

$configuracoes = [];

foreach ($consulta->fetchAll() as $linha) {
    $configuracoes[$linha['dia_semana']] = $linha;
}

?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Horários | Derma Beauty</title>

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

            <a href="horarios.php" class="ativo">Horários</a>

            <a href="index.php" class="sair">Sair</a>

        </nav>

    </aside>

    <main class="conteudo">

        <header class="cabecalho-pagina">

            <h1>Horários de funcionamento</h1>

            <p>
                Configure os dias e horários de atendimento da clínica.
            </p>

        </header>

        <?php if ($mensagem !== ''): ?>

            <p class="mensagem-sucesso">
                <?= htmlspecialchars($mensagem) ?>
            </p>

        <?php endif; ?>

        <?php if ($erro !== ''): ?>

            <p class="mensagem-erro">
                <?= htmlspecialchars($erro) ?>
            </p>

        <?php endif; ?>

        <section class="cartao">

            <h2>Configuração semanal</h2>

            <form method="post" action="horarios.php">

                <?php foreach ($dias as $numero => $nome):

                    $config = $configuracoes[$numero];

                ?>

                    <div class="linha-horario">

                        <label>

                            <input
                                type="checkbox"
                                name="ativo[<?= $numero ?>]"
                                value="1"
                                <?= $config['ativo'] ? 'checked' : '' ?>
                            >

                            <?= $nome ?>

                        </label>

                        <div class="campo">

                            <label>Abre às</label>

                            <input
                                type="time"
                                name="hora_inicio[<?= $numero ?>]"
                                value="<?= htmlspecialchars($config['hora_inicio']) ?>"
                                required
                            >

                        </div>

                        <div class="campo">

                            <label>Fecha às</label>

                            <input
                                type="time"
                                name="hora_fim[<?= $numero ?>]"
                                value="<?= htmlspecialchars($config['hora_fim']) ?>"
                                required
                            >

                        </div>

                        <div class="campo">

                            <label>Intervalo</label>

                            <select name="intervalo[<?= $numero ?>]">

                                <option value="15"
                                    <?= $config['intervalo_minutos'] == 15 ? 'selected' : '' ?>>
                                    15 minutos
                                </option>

                                <option value="30"
                                    <?= $config['intervalo_minutos'] == 30 ? 'selected' : '' ?>>
                                    30 minutos
                                </option>

                                <option value="60"
                                    <?= $config['intervalo_minutos'] == 60 ? 'selected' : '' ?>>
                                    1 hora
                                </option>

                            </select>

                        </div>

                    </div>

                <?php endforeach; ?>

                <button type="submit" class="botao">
                    Salvar horários
                </button>

            </form>

        </section>

    </main>

</div>

</body>
</html>
