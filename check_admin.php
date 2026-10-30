<?php
$pdo = new PDO('mysql:host=127.0.0.1;dbname=viera_bakery', 'root', '');

// Check admin user
$stmt = $pdo->query('SELECT id, name, email, role FROM users WHERE email = "admin@viera.test"');
$result = $stmt->fetch(PDO::FETCH_ASSOC);

echo "=== ADMIN USER CHECK ===\n";
if ($result) {
    echo "✓ Admin user found:\n";
    echo "  ID: " . $result['id'] . "\n";
    echo "  Name: " . $result['name'] . "\n";
    echo "  Email: " . $result['email'] . "\n";
    echo "  Role: " . $result['role'] . "\n";
} else {
    echo "✗ No admin user found with email: admin@viera.test\n";
}

// Check all users
$stmt = $pdo->query('SELECT COUNT(*) as count FROM users');
$count = $stmt->fetch(PDO::FETCH_ASSOC);
echo "\nTotal users in database: " . $count['count'] . "\n";

// List all users
$stmt = $pdo->query('SELECT id, name, email, role FROM users');
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "\n=== ALL USERS ===\n";
foreach ($users as $user) {
    echo "- ID: {$user['id']}, Name: {$user['name']}, Email: {$user['email']}, Role: {$user['role']}\n";
}
