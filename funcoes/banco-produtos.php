
<?php

require_once __DIR__ . '/../admin/config/conecta.php';

function listarTratamentos()
{
    global $pdo;

    $sql = "SELECT * FROM tratamentos ORDER BY id ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();

    return $stmt->fetchAll();
}

function buscarTratamento($id)
{
    global $pdo;

    $sql = "SELECT * FROM tratamentos WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);

    return $stmt->fetch();
}
