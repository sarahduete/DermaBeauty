<?php

require_once __DIR__ . '/../admin/config/conecta.php';

$email = 'admin@dermabeauty.com';

$stmt = $pdo->prepare(
    "SELECT senha FROM tbusuarios WHERE email = ?"
);

$stmt->execute([$email]);

$usuario = $stmt->fetch();

if (!$usuario) {
    exit('Administrador não encontrado.');
}

echo "Administrador encontrado.<br>";

echo "Hash armazenado: ";

echo password_get_info($usuario['senha'])['algoName'];

echo "<br>";

echo "Hash reconhecido pelo PHP!";
