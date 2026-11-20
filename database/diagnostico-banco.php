<?php

require_once __DIR__ . '/../admin/config/conecta.php';

echo "<h2>Diagnóstico do banco</h2>";

echo "Arquivo existe: ";
echo file_exists($caminhoBanco) ? "SIM" : "NÃO";

echo "<br>";

echo "Caminho real: ";
echo htmlspecialchars(realpath($caminhoBanco) ?: "Não encontrado");

echo "<br>";

echo "Tamanho: ";
echo file_exists($caminhoBanco)
    ? filesize($caminhoBanco) . " bytes"
    : "Indisponível";

echo "<br>";

$stmt = $pdo->query("SELECT COUNT(*) FROM tbusuarios");

echo "Administradores cadastrados: ";
echo $stmt->fetchColumn();

echo "<br><br>Conexão realizada!";
