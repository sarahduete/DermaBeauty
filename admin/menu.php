<?php

$paginaAtual = basename($_SERVER['PHP_SELF']);

$itensMenu = [
    'painel.php' => 'Painel',
    'produtos.php' => 'Tratamentos',
    'clientes.php' => 'Clientes',
    'pedidos.php' => 'Agendamentos',
    'horarios.php' => 'Horários'
];

?>

<aside class="menu-lateral">

    <h2 class="logo">Derma Beauty</h2>

    <nav>

        <?php foreach ($itensMenu as $arquivo => $nome): ?>

            <a
                href="<?= htmlspecialchars($arquivo, ENT_QUOTES, 'UTF-8') ?>"
                class="<?= $paginaAtual === $arquivo ? 'ativo' : '' ?>"
            >
                <?= htmlspecialchars($nome, ENT_QUOTES, 'UTF-8') ?>
            </a>

        <?php endforeach; ?>

        <a href="index.php" class="sair">
            Sair
        </a>

    </nav>

</aside>
