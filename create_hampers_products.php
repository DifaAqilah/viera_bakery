<?php
$pdo = new PDO('mysql:host=127.0.0.1;dbname=viera_bakery', 'root', '');

// Check products in Hampers category
$stmt = $pdo->query('SELECT id, name, category_id FROM products WHERE category_id = 4 ORDER BY id');
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "=== HAMPERS CATEGORY PRODUCTS (category_id = 4) ===\n";
if (empty($products)) {
    echo "✗ Tidak ada produk dalam kategori Hampers\n";
    echo "\nMembuat 4 produk Hampers...\n";
    
    $hampersProducts = [
        ['Hampers Lebaran Mix', 'hampers-lebaran-mix', 'Hampers spesial Lebaran dengan berbagai pilihan kue', 250000],
        ['Hampers Birthday Cake', 'hampers-birthday-cake', 'Hampers ulang tahun dengan kue lezat dan dekorasi cantik', 185000],
        ['Hampers Wedding Cookies', 'hampers-wedding-cookies', 'Hampers pernikahan dengan kue kering pilihan', 320000],
        ['Hampers Premium Box', 'hampers-premium-box', 'Hampers premium berisi berbagai kue terbaik kami', 450000],
    ];
    
    foreach ($hampersProducts as $product) {
        $stmt = $pdo->prepare('
            INSERT INTO products (category_id, name, slug, description, price, stock, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())
        ');
        $stmt->execute([4, $product[0], $product[1], $product[2], $product[3], 15]);
        echo "✓ {$product[0]} dibuat\n";
    }
} else {
    echo "✓ Total produk Hampers: " . count($products) . "\n";
    foreach ($products as $prod) {
        echo "- {$prod['name']} (ID: {$prod['id']})\n";
    }
}

// Check if hampers are showing correctly
$stmt = $pdo->prepare('SELECT id, name, category_id FROM products WHERE category_id = ? ORDER BY id DESC LIMIT 4');
$stmt->execute([4]);
$hampersList = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "\n=== HAMPERS UNTUK DITAMPILKAN (LATEST 4) ===\n";
foreach ($hampersList as $h) {
    echo "- {$h['name']} (category_id: {$h['category_id']})\n";
}
