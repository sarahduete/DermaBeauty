<?php

require_once __DIR__ . '/admin/config/conecta.php';

echo "<h2>Diagnóstico do banco</h2>";

echo "Caminho configurado: " . htmlspecialchars($caminhoBanco) . "<br>";

echo "Arquivo existe: " . (file_exists($caminhoBanco) ? "SIM" : "NÃO") . "<br>";

echo "Caminho real: " . htmlspecialchars(realpath($caminhoBanco) ?: "Não encontrado") . "<br>";

echo "Tamanho: " . (file_exists($caminhoBanco) ? filesize($caminhoBanco) . " bytes" : "Indisponível") . "<br>";

$stmt = $pdo->query("SELECT COUNT(*) FROM tbusuarios");
echo "Administradores cadastrados: " . $stmt->fetchColumn() . "<br>";

echo "<br>Conexão com o banco realizada!";
