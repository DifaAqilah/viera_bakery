<?php
try {
    $pdo = new PDO('mysql:host=127.0.0.1', 'root', '', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    
    echo "Dropping database viera-bakery...\n";
    $pdo->exec('DROP DATABASE IF EXISTS `viera-bakery`');
    
    echo "Creating fresh database viera-bakery...\n";
    $pdo->exec('CREATE DATABASE `viera-bakery` DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
    
    echo "✓ Database reset successfully!\n";
} catch(Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}
