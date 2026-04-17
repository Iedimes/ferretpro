<?php
require 'class/Database.php';
$db = Database::getInstance();
$products = $db->query("SELECT id, name, code, barcode FROM products WHERE barcode LIKE '%123456789%' LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);
echo "Products with barcode containing 123456789:\n";
foreach ($products as $p) {
    echo "ID: {$p['id']} - Name: {$p['name']} - Code: {$p['code']} - Barcode: {$p['barcode']}\n";
}
