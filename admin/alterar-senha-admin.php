
<?php

require_once __DIR__ . '/config/conecta.php';

$email = 'admin@dermabeauty.com';
$novaSenha = '12345678';

$senhaHash = password_hash($novaSenha, PASSWORD_DEFAULT);

$stmt = $pdo->prepare(
    'UPDATE tbusuarios SET senha = ? WHERE email = ?'
);

$stmt->execute([$senhaHash, $email]);

if ($stmt->rowCount() > 0) {
    echo 'Senha do administrador atualizada com sucesso!';
} else {
    echo 'Nenhum administrador encontrado com esse e-mail.';
}
