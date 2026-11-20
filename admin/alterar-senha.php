<?php

require_once __DIR__ . '/admin/config/conecta.php';

// ALTERE ESTES DADOS
$email = 'admin@dermabeauty.com';
$novaSenha = '12345678';

// Gera a senha criptografada
$senhaHash = password_hash($novaSenha, PASSWORD_DEFAULT);

// Atualiza a senha na tabela tbusuarios
$sql = "UPDATE tbusuarios SET senha = :senha WHERE email = :email";

$stmt = $pdo->prepare($sql);

$stmt->execute([
    ':senha' => $senhaHash,
    ':email' => $email
]);

if ($stmt->rowCount() > 0) {
    echo "Senha alterada com sucesso!";
} else {
    echo "Nenhum usuário encontrado com esse e-mail.";
}
