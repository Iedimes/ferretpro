<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once dirname(__DIR__) . '/class/Database.php';

$db = Database::getInstance();

echo "<h3>Generando datos de prueba...</h3>";

// Limpiar datos de prueba anteriores (mantener clientes y proveedores)
$db->exec("DELETE FROM sale_details");
$db->exec("DELETE FROM sales");
$db->exec("DELETE FROM accounts_receivable");
$db->exec("DELETE FROM accounts_payable");
$db->exec("DELETE FROM purchases");
$db->exec("DELETE FROM purchase_details");
$db->exec("DELETE FROM quotes");
$db->exec("DELETE FROM quote_details");
$db->exec("DELETE FROM credit_notes");
$db->exec("DELETE FROM credit_note_details");
$db->exec("DELETE FROM expenses");
$db->exec("UPDATE clients SET balance = 0");

echo "<p style='color:gray;'>Datos anteriores limpiados.</p>";

// Ventas de distintos tipos y fechas
$db->exec("INSERT INTO sales (user_id, client_id, type, payment_method, subtotal, discount, total, delivery_type, status, created_at) VALUES (1, 2, 'contado', 'efectivo', 250000, 0, 250000, 'mostrador', 'pagada', datetime('now', '-15 days'))");
$db->exec("INSERT INTO sales (user_id, client_id, type, payment_method, subtotal, discount, total, delivery_type, status, created_at) VALUES (1, 3, 'contado', 'transferencia', 450000, 0, 450000, 'mostrador', 'pagada', datetime('now', '-10 days'))");
$db->exec("INSERT INTO sales (user_id, client_id, type, payment_method, subtotal, discount, total, delivery_type, status, created_at) VALUES (1, 4, 'contado', 'qr', 180000, 0, 180000, 'mostrador', 'pagada', datetime('now', '-5 days'))");
$db->exec("INSERT INTO sales (user_id, client_id, type, payment_method, subtotal, discount, total, delivery_type, status, created_at) VALUES (1, 2, 'credito', 'efectivo', 320000, 0, 320000, 'mostrador', 'pendiente', datetime('now', '-3 days'))");
$db->exec("INSERT INTO sales (user_id, client_id, type, payment_method, subtotal, discount, total, delivery_type, status, created_at) VALUES (1, 3, 'contado', 'tarjeta', 550000, 0, 550000, 'mostrador', 'pagada', datetime('now', '-1 days'))");
$db->exec("INSERT INTO sales (user_id, client_id, type, payment_method, subtotal, discount, total, delivery_type, status, created_at) VALUES (1, NULL, 'contado', 'efectivo', 85000, 0, 85000, 'mostrador', 'pagada', datetime('now', '-8 days'))");
echo "<p>✅ Ventas insertadas (6 ventas)</p>";

// Cuentas por cobrar
$db->exec("INSERT INTO accounts_receivable (client_id, sale_id, amount, due_date, status, created_at) VALUES (2, 4, 320000, date('now', '+5 days'), 'pendiente', datetime('now', '-3 days'))");
$db->exec("INSERT INTO accounts_receivable (client_id, sale_id, amount, due_date, status, created_at) VALUES (3, NULL, 180000, date('now', '-2 days'), 'pendiente', datetime('now', '-12 days'))");
$db->exec("INSERT INTO accounts_receivable (client_id, sale_id, amount, due_date, status, created_at) VALUES (4, NULL, 450000, date('now', '+2 days'), 'pendiente', datetime('now', '-8 days'))");

$db->exec("UPDATE clients SET balance = 320000 WHERE id = 2");
$db->exec("UPDATE clients SET balance = 180000 WHERE id = 3");
$db->exec("UPDATE clients SET balance = 450000 WHERE id = 4");
echo "<p>✅ Cuentas por cobrar insertadas (3 cuentas)</p>";

// Compras a crédito
$db->exec("INSERT INTO purchases (provider_id, user_id, invoice_number, subtotal, discount, total, payment_method, status, created_at) VALUES (1, 1, 'F001-001', 800000, 0, 800000, 'credito', 'pending', datetime('now', '-7 days'))");
$db->exec("INSERT INTO purchases (provider_id, user_id, invoice_number, subtotal, discount, total, payment_method, status, created_at) VALUES (2, 1, 'F002-002', 450000, 0, 450000, 'credito', 'pending', datetime('now', '-4 days'))");

$db->exec("INSERT INTO accounts_payable (provider_id, purchase_id, amount, due_date, status, created_at) VALUES (1, 1, 800000, date('now', '+3 days'), 'pendiente', datetime('now', '-7 days'))");
$db->exec("INSERT INTO accounts_payable (provider_id, purchase_id, amount, due_date, status, created_at) VALUES (2, 2, 450000, date('now', '+10 days'), 'pendiente', datetime('now', '-4 days'))");
echo "<p>✅ Cuentas por pagar insertadas (2 cuentas)</p>";

// Cotizaciones
$db->exec("INSERT INTO quotes (client_id, client_name, client_document, user_id, validity_days, discount, subtotal, total, status, created_at, expires_at) VALUES (2, 'Juan Gómez', '20123456789', 1, 30, 0, 520000, 520000, 'pending', datetime('now', '-5 days'), date('now', '+25 days'))");
$db->exec("INSERT INTO quotes (client_id, client_name, client_document, user_id, validity_days, discount, subtotal, total, status, created_at, expires_at) VALUES (3, 'María López', '27123456789', 1, 15, 50000, 850000, 800000, 'pending', datetime('now', '-2 days'), date('now', '+13 days'))");
echo "<p>✅ Cotizaciones insertadas (2 cotizaciones)</p>";

// Notas de crédito
$db->exec("INSERT INTO credit_notes (sale_id, user_id, client_id, reason, subtotal, total, status, created_at) VALUES (1, 1, 2, 'producto defectuoso', 25000, 25000, 'pending', datetime('now', '-1 days'))");
echo "<p>✅ Notas de crédito insertadas (1 nota)</p>";

// Gastos
$db->exec("INSERT INTO expenses (user_id, category, description, amount, payment_method, cuenta, referencia, date, created_at) VALUES (1, 'Útiles', 'Papel, lapiceras, carpetas', 150000, 'efectivo', 'caja', '', date('now', '-3 days'), datetime('now', '-3 days'))");
$db->exec("INSERT INTO expenses (user_id, category, description, amount, payment_method, cuenta, referencia, date, created_at) VALUES (1, 'Servicios', 'Internet mensuales', 180000, 'transferencia', 'banco', 'TXN-12345', date('now', '-10 days'), datetime('now', '-10 days'))");
$db->exec("INSERT INTO expenses (user_id, category, description, amount, payment_method, cuenta, referencia, date, created_at) VALUES (1, 'Mantenimiento', 'Reparación de equipo', 85000, 'efectivo', 'caja', '', date('now', '-5 days'), datetime('now', '-5 days'))");
$db->exec("INSERT INTO expenses (user_id, category, description, amount, payment_method, cuenta, referencia, date, created_at) VALUES (1, 'Transporte', 'Flete de mercadería', 120000, 'transferencia', 'banco', 'TXN-12346', date('now', '-2 days'), datetime('now', '-2 days'))");
echo "<p>✅ Gastos insertados (4 gastos)</p>";

echo "<hr><p style='color:green;'><strong>Datos de prueba creados correctamente!</strong></p>";
echo "<a href='?page=dashboard' class='btn btn-primary'>Ir al Dashboard</a>";