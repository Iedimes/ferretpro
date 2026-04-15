<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once dirname(__DIR__) . '/class/Database.php';

echo "<h2>Test de Módulos FerrePro</h2><hr>";

$pages = [
    'dashboard' => 'Dashboard',
    'pos' => 'Punto de Venta',
    'sales' => 'Ventas',
    'products' => 'Productos',
    'clients' => 'Clientes',
    'providers' => 'Proveedores',
    'categories' => 'Categorías',
    'quotes' => 'Cotizaciones',
    'credit_notes' => 'Notas Crédito',
    'purchases' => 'Compras',
    'receivable' => 'Cuentas por Cobrar',
    'payable' => 'Cuentas por Pagar',
    'expenses' => 'Gastos',
    'cash' => 'Caja',
    'reports' => 'Reportes',
    'users' => 'Usuarios',
    'settings' => 'Configuración',
    'backup' => 'Backup'
];

$db = Database::getInstance();
$results = [];

foreach ($pages as $page => $name) {
    ob_start();
    try {
        $pageFile = dirname(__DIR__) . '/views/' . $page . '/index.php';
        
        if (!file_exists($pageFile)) {
            $results[] = "<tr><td>$name</td><td class='text-danger'>NO EXISTE VIEW</td></tr>";
            ob_end_clean();
            continue;
        }
        
        include $pageFile;
        
        $results[] = "<tr><td>$name</td><td class='text-success'>OK</td></tr>";
        ob_end_clean();
    } catch (Exception $e) {
        $results[] = "<tr><td>$name</td><td class='text-danger'>ERROR: " . $e->getMessage() . "</td></tr>";
        ob_end_clean();
    }
}

echo "<table class='table table-bordered'><thead><tr><th>Módulo</th><th>Estado</th></tr></thead><tbody>";
echo implode("\n", $results);
echo "</tbody></table>";

echo "<hr><h3>Test CRUDs</h3>";

function testCRUD($table, $name) {
    global $db;
    try {
        $count = $db->query("SELECT COUNT(*) as cnt FROM $table")->fetch(PDO::FETCH_ASSOC);
        return "<tr><td>$name</td><td>{$count['cnt']} registros</td><td class='text-success'>OK</td></tr>";
    } catch (Exception $e) {
        return "<tr><td>$name</td><td class='text-danger'>ERROR</td></tr>";
    }
}

echo "<table class='table table-bordered'><thead><tr><th>Tabla</th><th>Registros</th><th>Estado</th></tr></thead><tbody>";
echo testCRUD('sales', 'Ventas');
echo testCRUD('products', 'Productos');
echo testCRUD('clients', 'Clientes');
echo testCRUD('providers', 'Proveedores');
echo testCRUD('quotes', 'Cotizaciones');
echo testCRUD('credit_notes', 'Notas Crédito');
echo testCRUD('purchases', 'Compras');
echo testCRUD('accounts_receivable', 'Cuentas por Cobrar');
echo testCRUD('accounts_payable', 'Cuentas por Pagar');
echo testCRUD('expenses', 'Gastos');
echo testCRUD('cash_register', 'Caja');
echo testCRUD('users', 'Usuarios');
echo testCRUD('categories', 'Categorías');
echo "</tbody></table>";

echo "<hr><p style='color:green;'><strong>Test completado!</strong></p>";
