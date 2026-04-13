<?php
// Test file to debug POS issues
echo "<!DOCTYPE html>
<html>
<head>
    <title>POS Test</title>
    <style>
        body { font-family: Arial; margin: 20px; }
        .test { padding: 10px; margin: 10px 0; border: 1px solid #ccc; }
        .pass { background: #d4edda; }
        .fail { background: #f8d7da; }
    </style>
</head>
<body>
    <h1>Diagnóstico del POS</h1>";

// Test 1: Check PHP functions
echo "<div class='test pass'><strong>✓ PHP está funcionando</strong></div>";

// Test 2: Check database connection
require_once dirname(__DIR__) . '/../class/Database.php';
try {
    $db = Database::getInstance();
    echo "<div class='test pass'><strong>✓ BD conectada</strong></div>";
} catch (Exception $e) {
    echo "<div class='test fail'><strong>✗ Error BD:</strong> " . $e->getMessage() . "</div>";
}

// Test 3: Check products
$products = $db->query("SELECT COUNT(*) as count FROM products WHERE active = 1 AND stock > 0")->fetch(\PDO::FETCH_ASSOC);
echo "<div class='test" . ($products['count'] > 0 ? ' pass' : ' fail') . "'><strong>" . ($products['count'] > 0 ? '✓' : '✗') . " Productos activos:</strong> " . $products['count'] . "</div>";

// Test 4: JavaScript test
echo "<div class='test'>
    <h3>Test de JavaScript</h3>
    <input type='text' id='testInput' placeholder='Escribe aquí'>
    <button onclick='testClick()'>Click Test</button>
    <div id='testOutput'></div>
</div>";

echo "</body>
<script>
console.log('Scripts cargados correctamente');
console.log('posCart disponible:', typeof posCart !== 'undefined');
console.log('posLoadProducts disponible:', typeof posLoadProducts !== 'undefined');

function testClick() {
    document.getElementById('testOutput').innerHTML = '<div class=\"pass\">✓ JavaScript está funcionando</div>';
    console.log('Click detectado');
}
</script>
</html>";
?>
