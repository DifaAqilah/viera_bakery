<?php
echo "Testing MySQL IPv4 connection...\n";

try {
    $pdo = new PDO('mysql:host=127.0.0.1;port=3306', 'root', '', [
        PDO::ATTR_TIMEOUT => 2,
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    
    echo "✓ Connected to 127.0.0.1!\n";
    
    // Try to create database
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `viera-bakery`");
    echo "✓ Database created/already exists\n";
    
} catch(PDOException $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}


