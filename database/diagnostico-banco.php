
<?php

require_once __DIR__ . '/../admin/config/conecta.php';

$stmt = $pdo->query(
    "SELECT id, nome, email FROM tbusuarios"
);

$usuarios = $stmt->fetchAll();

echo "<h2>Administradores cadastrados</h2>";

foreach ($usuarios as $usuario) {
    echo "ID: " . (int) $usuario['id'] . "<br>";
    echo "Nome: " . htmlspecialchars($usuario['nome']) . "<br>";
    echo "E-mail: " . htmlspecialchars($usuario['email']) . "<br><hr>";
}
