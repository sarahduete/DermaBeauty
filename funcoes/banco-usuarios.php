<?php
// banco-usuarios.php: todo o SQL da tabela usuarios fica aqui.
// Cada função recebe o $pdo como primeiro parâmetro, porque uma função
// não enxerga as variáveis que foram criadas fora dela.

//READ: lista de usuários para a tabela da tela (sem a senha)
function listarUsuarios($pdo) {
    $consulta = $pdo->query('SELECT id, nome, email FROM usuarios ORDER BY nome');
    return $consulta->fetchAll();
}

//READ: Busca os dados do usuário por um id 
function buscarUsuario($pdo, $id) {
    $consulta = $pdo->prepare('SELECT id, nome, email FROM usuarios WHERE id = ?');
    $consulta-> execute([$id]);
    return $consulta->fech();
} 

//LOGIN: Efetuar o login do usuário no Banco de Dados 
function efetuarLogin($pdo, $email, $senha){
    $consulta = $pdo-> prepare('SELECT id, nome FROM usuarios where email = ? and senha = ?');
    $consulta->execute ([$email, $senha]);

    $usuario = $consulta->fetch(PDO::FETCH_ASSOC);

    if($usuario){
        return ['id' => $usuario['id'], 'nome' => $usuario['nome']];
    }
return false;
}

// CREATE: devolve true se gravou e false se o e-mail já existe
function criarUsuario($pdo, $nome, $email, $senha) {
    try {
        // a senha nunca vai pura para o banco
       // $hash = password_hash($senha, PASSWORD_DEFAULT);
        $inserir = $pdo->prepare('INSERT INTO usuarios (nome, email, senha) VALUES (?, ?, ?)');
        $inserir->execute([$nome, $email, $senha]);
        return true;
    } catch (PDOException $e) {
        // 23000 = violou uma regra do banco. Aqui, o e-mail repetido (UNIQUE)
        if ($e->getCode() == 23000) {
            return false;
        }
        throw $e;   // qualquer outro erro aparece normalmente
    }
}

// UPDATE: com a senha em branco, troca só nome e e-mail. Devolve false se o e-mail é de outro
function atualizarUsuario($pdo, $id, $nome, $email, $senha) {
    try {
        if ($senha === '') {
            $atualizar = $pdo->prepare('UPDATE usuarios SET nome = ?, email = ? WHERE id = ?');
            $atualizar->execute([$nome, $email, $id]);
        } else {
            $hash = password_hash($senha, PASSWORD_DEFAULT);
            $atualizar = $pdo->prepare('UPDATE usuarios SET nome = ?, email = ?, senha = ? WHERE id = ?');
            $atualizar->execute([$nome, $email, $hash, $id]);
        }
        return true;
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) {
            return false;
        }
        throw $e;
    }
}

// DELETE: sem o WHERE, apagaria TODOS os usuários
function excluirUsuario($pdo, $id) {
    $excluir = $pdo->prepare('DELETE FROM usuarios WHERE id = ?');
    $excluir->execute([$id]);
}