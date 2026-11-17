<?php
// banco-clientes.php: todo o SQL da tabela clientes fica aqui.
// Cada função recebe o $pdo como primeiro parâmetro.
// Os dados do cliente viajam num array ($dados): cada valor é achado pelo nome da chave,
// como $dados['cpf']. Assim não há como trocar a ordem dos oito campos.

// READ: lista de clientes para a tabela da tela
function listarClientes($pdo) {
    $consulta = $pdo->query('SELECT id, nome, email, telefone, cidade FROM clientes ORDER BY nome');
    return $consulta->fetchAll();
}

// READ: um cliente só, com todos os campos. Devolve o array ou false se o id não existir
function buscarCliente($pdo, $id) {
    $consulta = $pdo->prepare('SELECT * FROM clientes WHERE id = ?');
    $consulta->execute([$id]);
    return $consulta->fetch();
}

// CREATE: devolve true se gravou e false se o e-mail ou o CPF já existem
function criarCliente($pdo, $dados) {
    try {
        // os valores com dois-pontos (:nome) são parâmetros com nome
        $inserir = $pdo->prepare(
            'INSERT INTO clientes (nome, email, telefone, cpf, nascimento, cidade, estado, endereco)
             VALUES (:nome, :email, :telefone, :cpf, :nascimento, :cidade, :estado, :endereco)'
        );
        $inserir->execute([
            ':nome'       => $dados['nome'],
            ':email'      => $dados['email'],
            ':telefone'   => $dados['telefone'],
            ':cpf'        => $dados['cpf'],
            ':nascimento' => $dados['nascimento'],
            ':cidade'     => $dados['cidade'],
            ':estado'     => $dados['estado'],
            ':endereco'   => $dados['endereco']
        ]);
        return true;
    } catch (PDOException $e) {
        // 23000 = violou uma regra do banco. Aqui, e-mail ou CPF repetido (UNIQUE)
        if ($e->getCode() == 23000) {
            return false;
        }
        throw $e;   // qualquer outro erro aparece normalmente
    }
}

// UPDATE: troca os dados do cliente. Devolve false se o e-mail ou o CPF já são de outro
function atualizarCliente($pdo, $id, $dados) {
    try {
        $atualizar = $pdo->prepare(
            'UPDATE clientes
             SET nome = :nome, email = :email, telefone = :telefone, cpf = :cpf,
                 nascimento = :nascimento, cidade = :cidade, estado = :estado, endereco = :endereco
             WHERE id = :id'
        );
        $atualizar->execute([
            ':nome'       => $dados['nome'],
            ':email'      => $dados['email'],
            ':telefone'   => $dados['telefone'],
            ':cpf'        => $dados['cpf'],
            ':nascimento' => $dados['nascimento'],
            ':cidade'     => $dados['cidade'],
            ':estado'     => $dados['estado'],
            ':endereco'   => $dados['endereco'],
            ':id'         => $id
        ]);
        return true;
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) {
            return false;
        }
        throw $e;
    }
}

// DELETE: sem o WHERE, apagaria TODOS os clientes
function excluirCliente($pdo, $id) {
    $excluir = $pdo->prepare('DELETE FROM clientes WHERE id = ?');
    $excluir->execute([$id]);
}