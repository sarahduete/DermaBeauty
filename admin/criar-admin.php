<?php

require_once __DIR__ . '/config/conecta.php';

$nome = 'Administrador';
$email = 'admin.dermabeauty@gmail.com';
$senha = '12345678';

try {
    // Verifica se o e-mail já existe
    $consulta = $pdo->prepare(
        "SELECT id FROM tbusuarios WHERE email = :email"
    );

    $consulta->execute(['email' => $email]);

    if ($consulta->fetch()) {
        exit('Este e-mail já está cadastrado.');
    }

    // Cria o administrador
    $senhaHash = password_hash($senha, PASSWORD_DEFAULT);

    $inserir = $pdo->prepare(
        "INSERT INTO tbusuarios (nome, email, senha)
         VALUES (:nome, :email, :senha)"
    );

    $inserir->execute([
        'nome' => $nome,
        'email' => $email,
        'senha' => $senhaHash
    ]);

    echo 'Administrador criado com sucesso!';

} catch (PDOException $e) {
    echo 'Erro ao criar administrador.';
}
