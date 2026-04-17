<?php
require_once dirname(__DIR__) . '/class/Database.php';
require_once dirname(__DIR__) . '/actions/helpers.php';

echo "=== Productos sin imagen ===\n";
$products = db()->query("SELECT id, code, name FROM products WHERE active = 1 AND (image IS NULL OR image = '') ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);

foreach ($products as $p) {
    echo "ID: {$p['id']} | {$p['code']} | {$p['name']}\n";
}

echo "\nTotal: " . count($products) . " productos sin imagen\n";