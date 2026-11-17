<?php

require_once __DIR__ . '/../admin/config/conecta.php';

$tabelas = ['clientes', 'tratamentos', 'agendamentos'];

foreach ($tabelas as $tabela) {
    echo "<h2>Tabela: $tabela</h2>";

    $consulta = $pdo->query("PRAGMA table_info('$tabela')");

    foreach ($consulta->fetchAll() as $coluna) {
        echo $coluna['name'] . " — " . $coluna['type'] . "<br>";
    }
}
?>