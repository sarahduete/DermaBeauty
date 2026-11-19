<?php

require_once __DIR__ . '/../admin/config/conecta.php';

function colunaExiste(PDO $pdo, string $tabela, string $coluna): bool
{
    $colunas = $pdo->query("PRAGMA table_info($tabela)")
                   ->fetchAll(PDO::FETCH_ASSOC);

    foreach ($colunas as $item) {
        if ($item['name'] === $coluna) {
            return true;
        }
    }

    return false;
}

try {

    // Adiciona duração aos tratamentos
    if (!colunaExiste($pdo, 'tratamentos', 'duracao_minutos')) {
        $pdo->exec(
            "ALTER TABLE tratamentos
             ADD COLUMN duracao_minutos INTEGER NOT NULL DEFAULT 60"
        );
    }

    // Adiciona horário aos agendamentos
    if (!colunaExiste($pdo, 'agendamentos', 'horario')) {
        $pdo->exec(
            "ALTER TABLE agendamentos
             ADD COLUMN horario TEXT"
        );
    }

    // Cria tabela de funcionamento
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS horarios_funcionamento (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            dia_semana INTEGER NOT NULL UNIQUE,
            hora_inicio TEXT NOT NULL,
            hora_fim TEXT NOT NULL,
            intervalo_minutos INTEGER NOT NULL DEFAULT 30,
            ativo INTEGER NOT NULL DEFAULT 0
        )
    ");

    // Configuração inicial
    $inserir = $pdo->prepare("
        INSERT OR IGNORE INTO horarios_funcionamento
        (dia_semana, hora_inicio, hora_fim, intervalo_minutos, ativo)
        VALUES (?, ?, ?, 30, ?)
    ");

    for ($dia = 1; $dia <= 7; $dia++) {

        $ativo = ($dia >= 1 && $dia <= 5) ? 1 : 0;

        $inserir->execute([
            $dia,
            '08:00',
            '18:00',
            $ativo
        ]);
    }

    echo "Agenda configurada com sucesso!";
    
} catch (PDOException $e) {
    die("Erro ao configurar a agenda: " . $e->getMessage());
}
