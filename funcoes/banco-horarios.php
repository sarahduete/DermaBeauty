
<?php

function horarioParaMinutos(string $horario): int
{
    $partes = explode(':', $horario);

    if (count($partes) !== 2) {
        return -1;
    }

    return ((int) $partes[0] * 60) + (int) $partes[1];
}

function dataValida(string $data): bool
{
    $objeto = DateTimeImmutable::createFromFormat('!Y-m-d', $data);

    return $objeto && $objeto->format('Y-m-d') === $data;
}

function listarHorariosDisponiveis(
    PDO $pdo,
    int $tratamentoId,
    string $data
): array {

    if (!dataValida($data)) {
        return [];
    }

    $hoje = new DateTimeImmutable('today');
    $dataEscolhida = new DateTimeImmutable($data);

    if ($dataEscolhida < $hoje) {
        return [];
    }

    // Identifica o dia da semana
    $diaSemana = (int) $dataEscolhida->format('N');

    $consulta = $pdo->prepare("
        SELECT hora_inicio, hora_fim, intervalo_minutos
        FROM horarios_funcionamento
        WHERE dia_semana = ? AND ativo = 1
    ");

    $consulta->execute([$diaSemana]);

    $funcionamento = $consulta->fetch();

    if (!$funcionamento) {
        return [];
    }

    // Busca duração do tratamento
    $consulta = $pdo->prepare("
        SELECT duracao_minutos
        FROM tratamentos
        WHERE id = ?
    ");

    $consulta->execute([$tratamentoId]);

    $tratamento = $consulta->fetch();

    if (!$tratamento) {
        return [];
    }

    $duracao = max(1, (int) $tratamento['duracao_minutos']);

    $intervalo = max(
        1,
        (int) $funcionamento['intervalo_minutos']
    );

    $inicio = horarioParaMinutos($funcionamento['hora_inicio']);
    $fim = horarioParaMinutos($funcionamento['hora_fim']);

    if ($inicio < 0 || $fim <= $inicio) {
        return [];
    }

    // Busca agendamentos existentes
    $consulta = $pdo->prepare("
        SELECT
            a.horario,
            a.data_agendamento,
            COALESCE(t.duracao_minutos, 60) AS duracao_minutos

        FROM agendamentos a

        LEFT JOIN tratamentos t
            ON t.id = a.tratamento_id

        WHERE substr(a.data_agendamento, 1, 10) = ?

        AND LOWER(a.status) NOT IN (
            'cancelado',
            'cancelada'
        )
    ");

    $consulta->execute([$data]);

    $agendamentos = $consulta->fetchAll();

    $ocupados = [];

    foreach ($agendamentos as $agendamento) {

        $hora = trim($agendamento['horario'] ?? '');

        // Compatibilidade com registros antigos
        if (
            $hora === '' &&
            strlen($agendamento['data_agendamento']) >= 16
        ) {
            $hora = substr(
                $agendamento['data_agendamento'],
                11,
                5
            );
        }

        $inicioOcupado = horarioParaMinutos($hora);

        if ($inicioOcupado >= 0) {

            $ocupados[] = [
                'inicio' => $inicioOcupado,
                'fim' => $inicioOcupado +
                    max(1, (int) $agendamento['duracao_minutos'])
            ];
        }
    }

    $disponiveis = [];

    $agora = new DateTimeImmutable();

    // Calcula os horários
    for (
        $minuto = $inicio;
        $minuto + $duracao <= $fim;
        $minuto += $intervalo
    ) {

        $fimSlot = $minuto + $duracao;

        $horaTexto = sprintf(
            '%02d:%02d',
            intdiv($minuto, 60),
            $minuto % 60
        );

        $dataHora = new DateTimeImmutable(
            $data . ' ' . $horaTexto . ':00'
        );

        if (
            $dataEscolhida == $hoje &&
            $dataHora <= $agora
        ) {
            continue;
        }

        $conflito = false;

        foreach ($ocupados as $ocupado) {

            if (
                $minuto < $ocupado['fim'] &&
                $fimSlot > $ocupado['inicio']
            ) {
                $conflito = true;
                break;
            }
        }

        if (!$conflito) {
            $disponiveis[] = $horaTexto;
        }
    }

    return $disponiveis;
}


// Registra o agendamento
function criarAgendamentoCliente(
    PDO $pdo,
    int $clienteId,
    int $tratamentoId,
    string $data,
    string $horario
): int {

    if (!preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $horario)) {
        throw new InvalidArgumentException('Horário inválido.');
    }

    $pdo->beginTransaction();

    try {

        // Confere novamente se o horário está disponível
        $disponiveis = listarHorariosDisponiveis(
            $pdo,
            $tratamentoId,
            $data
        );

        if (!in_array($horario, $disponiveis, true)) {
            throw new RuntimeException(
                'Horário indisponível. Escolha outro.'
            );
        }

        $consulta = $pdo->prepare("
            SELECT preco
            FROM tratamentos
            WHERE id = ?
        ");

        $consulta->execute([$tratamentoId]);

        $tratamento = $consulta->fetch();

        if (!$tratamento) {
            throw new RuntimeException('Tratamento não encontrado.');
        }

        $inserir = $pdo->prepare("
            INSERT INTO agendamentos
            (
                cliente_id,
                tratamento_id,
                data_agendamento,
                horario,
                status,
                valor
            )
            VALUES (?, ?, ?, ?, 'Pendente', ?)
        ");

        $inserir->execute([
            $clienteId,
            $tratamentoId,
            $data . ' ' . $horario . ':00',
            $horario,
            $tratamento['preco']
        ]);

        $id = (int) $pdo->lastInsertId();

        $pdo->commit();

        return $id;

    } catch (Throwable $e) {

        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        throw $e;
    }
}
