<?php

$caminhoBanco = __DIR__ . '/../../database/dermabeauty.db';

$pdo = new PDO('sqlite:' . $caminhoBanco);

$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

$pdo->exec('PRAGMA foreign_keys = ON');
$pdo->exec('PRAGMA busy_timeout = 10000');
?>

