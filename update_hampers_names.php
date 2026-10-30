<?php
$pdo = new PDO('mysql:host=127.0.0.1;dbname=viera_bakery', 'root', '');

// Update Hampers products names
$hampersUpdates = [
    [2, 'Hampers Lebaran Mix', 'hampers-lebaran-mix', 'Hampers spesial Lebaran dengan berbagai pilihan kue'],
    [3, 'Hampers Birthday Cake', 'hampers-birthday-cake', 'Hampers ulang tahun dengan kue lezat dan dekorasi cantik'],
    [4, 'Hampers Wedding Cookies', 'hampers-wedding-cookies', 'Hampers pernikahan dengan kue kering pilihan'],
];

echo "=== UPDATING HAMPERS PRODUCTS ===\n";
foreach ($hampersUpdates as $update) {
    $stmt = $pdo->prepare('
        UPDATE products 
        SET name = ?, slug = ?, description = ?, updated_at = NOW()
        WHERE id = ?
    ');
    $stmt->execute([$update[1], $update[2], $update[3], $update[0]]);
    echo "✓ Product ID {$update[0]} updated to: {$update[1]}\n";
}

// Also create one more hampers product if needed
$stmt = $pdo->query('SELECT COUNT(*) as count FROM products WHERE category_id = 4');
$count = $stmt->fetch(PDO::FETCH_ASSOC);

if ($count['count'] < 4) {
    echo "\nMembuat produk Hampers tambahan...\n";
    $stmt = $pdo->prepare('
        INSERT INTO products (category_id, name, slug, description, price, stock, created_at, updated_at)
        VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())
    ');
    $stmt->execute([4, 'Hampers Premium Box', 'hampers-premium-box', 'Hampers premium berisi berbagai kue terbaik kami', 450000, 20]);
    echo "✓ Hampers Premium Box dibuat\n";
}

// Verify
$stmt = $pdo->query('SELECT id, name, category_id FROM products WHERE category_id = 4 ORDER BY id');
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "\n=== VERIFIED HAMPERS PRODUCTS ===\n";
foreach ($products as $prod) {
    echo "- {$prod['name']}\n";
}
