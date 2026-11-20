
<?php

require_once __DIR__ . '/config/conecta.php';

// DADOS PARA ALTERAÇÃO
$email = 'admin@dermabeauty.com';
$novaSenha = '12345678';

// CHAVE DE SEGURANÇA TEMPORÁRIA
$chave = 'TROQUE_POR_UMA_CHAVE_LONGA_E_SECRETA';

if (
    !isset($_GET['chave']) ||
    !hash_equals($chave, $_GET['chave'])
) {
    http_response_code(403);
    exit('Acesso negado.');
}

try {

    $stmt = $pdo->prepare(
        'SELECT id FROM tbusuarios WHERE email = ? LIMIT 1'
    );

    $stmt->execute([$email]);
    $usuario = $stmt->fetch();

    if (!$usuario) {
        exit('Administrador não encontrado.');
    }

    $hash = password_hash($novaSenha, PASSWORD_DEFAULT);

    $stmt = $pdo->prepare(
        'UPDATE tbusuarios SET senha = ? WHERE id = ?'
    );

    $stmt->execute([$hash, $usuario['id']]);

    $verificar = $pdo->prepare(
        'SELECT senha FROM tbusuarios WHERE id = ?'
    );

    $verificar->execute([$usuario['id']]);
    $senhaSalva = $verificar->fetchColumn();

    if (password_verify($novaSenha, $senhaSalva)) {
        echo 'Senha alterada e verificada com sucesso!';
    } else {
        echo 'Não foi possível confirmar a alteração.';
    }

} catch (PDOException $e) {

    error_log('Erro ao alterar senha: ' . $e->getMessage());
    echo 'Erro ao atualizar a senha.';
}
