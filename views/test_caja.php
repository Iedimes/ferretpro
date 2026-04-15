<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once dirname(__DIR__) . '/class/Database.php';

$db = Database::getInstance();

echo "<h3>Generando datos de CAJA...</h3>";

// Limpiar caja
$db->exec("DELETE FROM cash_movements");
$db->exec("DELETE FROM cash_register");

echo "<p style='color:gray;'>Caja limpiada.</p>";

// ========== MARZO 2026 ==========
echo "<p>Generando caja de Marzo 2026...</p>";

// Aperturas de caja Marzo (todos los días)
$marzoDias = ['01','02','03','05','07','10','12','15','18','20','22','25','28','31'];
foreach ($marzoDias as $dia) {
    // Apertura
    $db->exec("INSERT INTO cash_register (user_id, opening_amount, opening_notes, status, opened_at, closed_at) VALUES (1, 5000000, 'Apertura diaria', 'closed', '2026-03-$dia 08:00:00', '2026-03-$dia 18:00:00')");
}

// ========== ABRIL 2026 ==========
echo "<p>Generando caja de Abril 2026...</p>";

$abrilDias = ['01','02','03','05','07','08','10','12','14','15'];
foreach ($abrilDias as $dia) {
    // Apertura
    $db->exec("INSERT INTO cash_register (user_id, opening_amount, opening_notes, status, opened_at, closed_at) VALUES (1, 5000000, 'Apertura diaria', 'closed', '2026-04-$dia 08:00:00', '2026-04-$dia 18:00:00')");
}

// Ahora agregar movimientos (ventas cobradas + gastos)
// Marzo - ventas en efectivo van a caja
$ventasEfecMarzo = [
    ['01',2500000], ['05',1850000], ['07',3200000], ['18',1950000], ['28',2850000]
];
foreach ($ventasEfecMarzo as $v) {
    $db->exec("INSERT INTO cash_movements (cash_register_id, user_id, type, amount, description, payment_method, cuenta, referencia, created_at) 
        VALUES (1, 1, 'in', {$v[1]}, 'Venta efectiva', 'efectivo', 'caja', '', '2026-03-{$v[0]} 10:00:00')");
}

// Marzo - gastos de caja
$gastosCajaMarzo = [
    ['01',150000], ['10',85000], ['28',280000]
];
foreach ($gastosCajaMarzo as $g) {
    $db->exec("INSERT INTO cash_movements (cash_register_id, user_id, type, amount, description, payment_method, cuenta, referencia, created_at) 
        VALUES (1, 1, 'out', {$g[1]}, 'Gasto', 'efectivo', 'caja', '', '2026-03-{$g[0]} 12:00:00')");
}

// Marzo - gastos de banco
$gastosBancoMarzo = [
    ['01',180000], ['20',120000]
];
foreach ($gastosBancoMarzo as $g) {
    $db->exec("INSERT INTO cash_movements (cash_register_id, user_id, type, amount, description, payment_method, cuenta, referencia, created_at) 
        VALUES (1, 1, 'out', {$g[1]}, 'Gasto servicios', 'transferencia', 'banco', 'TXN-001', '2026-03-{$g[0]} 12:00:00')");
}

// Abril - ventas efectivo
$ventasEfecAbril = [
    ['01',3500000], ['07',2950000], ['14',3200000]
];
foreach ($ventasEfecAbril as $v) {
    $db->exec("INSERT INTO cash_movements (cash_register_id, user_id, type, amount, description, payment_method, cuenta, referencia, created_at) 
        VALUES (2, 1, 'in', {$v[1]}, 'Venta efectiva', 'efectivo', 'caja', '', '2026-04-{$v[0]} 10:00:00')");
}

// Abril - gastos caja
$gastosCajaAbril = [
    ['08',320000], ['14',185000]
];
foreach ($gastosCajaAbril as $g) {
    $db->exec("INSERT INTO cash_movements (cash_register_id, user_id, type, amount, description, payment_method, cuenta, referencia, created_at) 
        VALUES (2, 1, 'out', {$g[1]}, 'Gasto', 'efectivo', 'caja', '', '2026-04-{$g[0]} 12:00:00')");
}

// Abril - gastos banco
$gastosBancoAbril = [
    ['01',65000], ['12',480000]
];
foreach ($gastosBancoAbril as $g) {
    $db->exec("INSERT INTO cash_movements (cash_register_id, user_id, type, amount, description, payment_method, cuenta, referencia, created_at) 
        VALUES (2, 1, 'out', {$g[1]}, 'Gasto servicios', 'transferencia', 'banco', 'TXN-101', '2026-04-{$g[0]} 12:00:00')");
}

echo "<p>✅ Datos de caja generados!</p>";
echo "<hr>";
echo "<p><strong>Periodo:</strong> Marzo + Abril 2026</p>";
echo "<p><strong>Aperturas:</strong> 13 en marzo, 10 en abril</p>";
echo "<p><strong>Movimientos:</strong> Entradas y salidas por efectivo y banco</p>";
echo "<a href='?page=cash' class='btn btn-primary'>Ir a Caja</a>";