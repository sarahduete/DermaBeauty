<?php

require_once __DIR__ . '/../admin/config/conecta.php';

function listarAgendamentos()
{
    global $pdo;

    $sql = "SELECT * FROM agendamentos ORDER BY id ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();

    return $stmt->fetchAll();
}

function buscarAgendamento($id)
{
    global $pdo;

    $sql = "SELECT * FROM agendamentos WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);

    return $stmt->fetch();
}
