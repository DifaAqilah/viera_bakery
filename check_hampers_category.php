UUUUUU<?php
$pdo = new PDO('mysql:host=127.0.0.1;dbname=viera_bakery', 'root', '');

// Check existing categories
$stmt = $pdo->query('SELECT id, name FROM categories ORDER BY name');
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "=== EXISTING CATEGORIES ===\n";
foreach ($categories as $cat) {
    echo "- ID: {$cat['id']}, Name: {$cat['name']}\n";
}

// Check if Hampers category exists
$stmt = $pdo->query('SELECT id FROM categories WHERE name = "Hampers"');
$hampers = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$hampers) {
    echo "\n✗ Kategori 'Hampers' belum ada\n";
    echo "Membuat kategori Hampers...\n";
    
    $stmt = $pdo->prepare('INSERT INTO categories (name, slug, description, created_at, updated_at) 
                          VALUES (?, ?, ?, NOW(), NOW())');
    $stmt->execute(['Hampers', 'hampers', 'Hampers istimewa untuk berbagai momen spesial']);
    
    echo "✓ Kategori Hampers berhasil dibuat\n";
} else {
    echo "\n✓ Kategori 'Hampers' sudah ada dengan ID: {$hampers['id']}\n";
}
