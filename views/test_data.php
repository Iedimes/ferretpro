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
$db->exec("DELETE FROM cash_movements");
$db->exec("DELETE FROM cash_register");
$db->exec("UPDATE clients SET balance = 0");

echo "<p style='color:gray;'>Datos anteriores limpiados.</p>";

// ========== MARZO 2026 (Mes pasado) ==========
echo "<p>Generando datos de Marzo 2026...</p>";

// Ventas Marzo
$db->exec("INSERT INTO sales (user_id, client_id, type, payment_method, subtotal, discount, total, delivery_type, status, created_at) VALUES (1, 2, 'contado', 'efectivo', 250000, 0, 250000, 'mostrador', 'pagada', '2026-03-01 09:00:00')");
$db->exec("INSERT INTO sales (user_id, client_id, type, payment_method, subtotal, discount, total, delivery_type, status, created_at) VALUES (1, 3, 'contado', 'transferencia', 450000, 0, 450000, 'mostrador', 'pagada', '2026-03-02 10:30:00')");
$db->exec("INSERT INTO sales (user_id, client_id, type, payment_method, subtotal, discount, total, delivery_type, status, created_at) VALUES (1, 4, 'contado', 'qr', 180000, 0, 180000, 'mostrador', 'pagada', '2026-03-03 14:00:00')");
$db->exec("INSERT INTO sales (user_id, client_id, type, payment_method, subtotal, discount, total, delivery_type, status, created_at) VALUES (1, 2, 'contado', 'efectivo', 85000, 0, 85000, 'mostrador', 'pagada', '2026-03-05 11:00:00')");
$db->exec("INSERT INTO sales (user_id, client_id, type, payment_method, subtotal, discount, total, delivery_type, status, created_at) VALUES (1, NULL, 'contado', 'efectivo', 120000, 0, 120000, 'mostrador', 'pagada', '2026-03-07 16:00:00')");
$db->exec("INSERT INTO sales (user_id, client_id, type, payment_method, subtotal, discount, total, delivery_type, status, created_at) VALUES (1, 3, 'credito', 'efectivo', 320000, 0, 320000, 'mostrador', 'pendiente', '2026-03-10 10:00:00')");
$db->exec("INSERT INTO sales (user_id, client_id, type, payment_method, subtotal, discount, total, delivery_type, status, created_at) VALUES (1, 4, 'contado', 'tarjeta', 550000, 0, 550000, 'mostrador', 'pagada', '2026-03-12 15:00:00')");
$db->exec("INSERT INTO sales (user_id, client_id, type, payment_method, subtotal, discount, total, delivery_type, status, created_at) VALUES (1, 2, 'contado', 'transferencia', 280000, 0, 280000, 'mostrador', 'pagada', '2026-03-15 09:30:00')");
$db->exec("INSERT INTO sales (user_id, client_id, type, payment_method, subtotal, discount, total, delivery_type, status, created_at) VALUES (1, NULL, 'contado', 'efectivo', 95000, 0, 95000, 'mostrador', 'pagada', '2026-03-18 14:00:00')");
$db->exec("INSERT INTO sales (user_id, client_id, type, payment_method, subtotal, discount, total, delivery_type, status, created_at) VALUES (1, 3, 'contado', 'qr', 150000, 0, 150000, 'mostrador', 'pagada', '2026-03-20 11:00:00')");
$db->exec("INSERT INTO sales (user_id, client_id, type, payment_method, subtotal, discount, total, delivery_type, status, created_at) VALUES (1, 4, 'contado', 'efectivo', 420000, 0, 420000, 'mostrador', 'pagada', '2026-03-22 16:30:00')");
$db->exec("INSERT INTO sales (user_id, client_id, type, payment_method, subtotal, discount, total, delivery_type, status, created_at) VALUES (1, 2, 'contado', 'transferencia', 180000, 0, 180000, 'mostrador', 'pagada', '2026-03-25 10:00:00')");
$db->exec("INSERT INTO sales (user_id, client_id, type, payment_method, subtotal, discount, total, delivery_type, status, created_at) VALUES (1, NULL, 'contado', 'efectivo', 65000, 0, 65000, 'mostrador', 'pagada', '2026-03-28 15:00:00')");
$db->exec("INSERT INTO sales (user_id, client_id, type, payment_method, subtotal, discount, total, delivery_type, status, created_at) VALUES (1, 3, 'contado', 'tarjeta', 380000, 0, 380000, 'mostrador', 'pagada', '2026-03-31 14:00:00')");

// CxC de Marzo (una pendiente)
$db->exec("INSERT INTO accounts_receivable (client_id, sale_id, amount, due_date, status, created_at) VALUES (3, 6, 320000, '2026-04-10', 'pendiente', '2026-03-10 10:00:00')");

// Compras Marzo
$db->exec("INSERT INTO purchases (provider_id, user_id, invoice_number, subtotal, discount, total, payment_method, status, created_at) VALUES (1, 1, 'F001-001', 800000, 0, 800000, 'credito', 'pending', '2026-03-05 10:00:00')");
$db->exec("INSERT INTO purchases (provider_id, user_id, invoice_number, subtotal, discount, total, payment_method, status, created_at) VALUES (2, 1, 'F001-002', 450000, 0, 450000, 'credito', 'pending', '2026-03-15 10:00:00')");

// CxP de Marzo (vencidas ya)
$db->exec("INSERT INTO accounts_payable (provider_id, purchase_id, amount, due_date, status, created_at) VALUES (1, 1, 800000, '2026-04-01', 'pendiente', '2026-03-05 10:00:00')");
$db->exec("INSERT INTO accounts_payable (provider_id, purchase_id, amount, due_date, status, created_at) VALUES (2, 2, 450000, '2026-04-10', 'pendiente', '2026-03-15 10:00:00')");

// Gastos Marzo
$db->exec("INSERT INTO expenses (user_id, category, description, amount, payment_method, cuenta, referencia, date, created_at) VALUES (1, 'Utiles', 'Papel, lapiceras, carpetas', 150000, 'efectivo', 'caja', '', '2026-03-01', '2026-03-01 08:00:00')");
$db->exec("INSERT INTO expenses (user_id, category, description, amount, payment_method, cuenta, referencia, date, created_at) VALUES (1, 'Servicios', 'Internet mensual', 180000, 'transferencia', 'banco', 'TXN-001', '2026-03-01', '2026-03-01 08:00:00')");
$db->exec("INSERT INTO expenses (user_id, category, description, amount, payment_method, cuenta, referencia, date, created_at) VALUES (1, 'Mantenimiento', 'Reparacion de equipo', 85000, 'efectivo', 'caja', '', '2026-03-10', '2026-03-10 09:00:00')");
$db->exec("INSERT INTO expenses (user_id, category, description, amount, payment_method, cuenta, referencia, date, created_at) VALUES (1, 'Transporte', 'Flete de mercaderia', 120000, 'transferencia', 'banco', 'TXN-002', '2026-03-20', '2026-03-20 10:00:00')");

echo "<p>✅ Datos de Marzo 2026 insertados</p>";

// ========== ABRIL 2026 (Mes actual) ==========
echo "<p>Generando datos de Abril 2026...</p>";

// Ventas Abril
$db->exec("INSERT INTO sales (user_id, client_id, type, payment_method, subtotal, discount, total, delivery_type, status, created_at) VALUES (1, 2, 'contado', 'efectivo', 350000, 0, 350000, 'mostrador', 'pagada', '2026-04-01 09:00:00')");
$db->exec("INSERT INTO sales (user_id, client_id, type, payment_method, subtotal, discount, total, delivery_type, status, created_at) VALUES (1, 3, 'contado', 'transferencia', 520000, 0, 520000, 'mostrador', 'pagada', '2026-04-02 10:30:00')");
$db->exec("INSERT INTO sales (user_id, client_id, type, payment_method, subtotal, discount, total, delivery_type, status, created_at) VALUES (1, 4, 'contado', 'qr', 280000, 0, 280000, 'mostrador', 'pagada', '2026-04-03 14:00:00')");
$db->exec("INSERT INTO sales (user_id, client_id, type, payment_method, subtotal, discount, total, delivery_type, status, created_at) VALUES (1, 2, 'contado', 'tarjeta', 420000, 0, 420000, 'mostrador', 'pagada', '2026-04-05 11:00:00')");
$db->exec("INSERT INTO sales (user_id, client_id, type, payment_method, subtotal, discount, total, delivery_type, status, created_at) VALUES (1, NULL, 'contado', 'efectivo', 95000, 0, 95000, 'mostrador', 'pagada', '2026-04-07 16:00:00')");
$db->exec("INSERT INTO sales (user_id, client_id, type, payment_method, subtotal, discount, total, delivery_type, status, created_at) VALUES (1, 3, 'credito', 'efectivo', 450000, 0, 450000, 'mostrador', 'pendiente', '2026-04-08 10:00:00')");
$db->exec("INSERT INTO sales (user_id, client_id, type, payment_method, subtotal, discount, total, delivery_type, status, created_at) VALUES (1, 4, 'contado', 'transferencia', 380000, 0, 380000, 'mostrador', 'pagada', '2026-04-10 15:00:00')");
$db->exec("INSERT INTO sales (user_id, client_id, type, payment_method, subtotal, discount, total, delivery_type, status, created_at) VALUES (1, 2, 'contado', 'qr', 220000, 0, 220000, 'mostrador', 'pagada', '2026-04-12 09:30:00')");
$db->exec("INSERT INTO sales (user_id, client_id, type, payment_method, subtotal, discount, total, delivery_type, status, created_at) VALUES (1, NULL, 'contado', 'efectivo', 125000, 0, 125000, 'mostrador', 'pagada', '2026-04-14 14:00:00')");

// CxC Abril (para prox 7 dias y mas)
$db->exec("INSERT INTO accounts_receivable (client_id, sale_id, amount, due_date, status, created_at) VALUES (3, 20, 450000, '2026-04-20', 'pendiente', '2026-04-08 10:00:00')");

// Cotizaciones Abril
$db->exec("INSERT INTO quotes (client_id, client_name, client_document, user_id, validity_days, discount, subtotal, total, status, created_at, expires_at) VALUES (2, 'Juan Gomez', '20123456789', 1, 30, 0, 520000, 520000, 'pending', '2026-04-05', '2026-05-05')");
$db->exec("INSERT INTO quotes (client_id, client_name, client_document, user_id, validity_days, discount, subtotal, total, status, created_at, expires_at) VALUES (3, 'Maria Lopez', '27123456789', 1, 15, 50000, 850000, 800000, 'pending', '2026-04-10', '2026-04-25')");

// Notas de credito Abril
$db->exec("INSERT INTO credit_notes (sale_id, user_id, client_id, reason, subtotal, total, status, created_at) VALUES (15, 1, 2, 'producto defectuoso', 25000, 25000, 'pending', '2026-04-12')");

// Gastos Abril
$db->exec("INSERT INTO expenses (user_id, category, description, amount, payment_method, cuenta, referencia, date, created_at) VALUES (1, 'Servicios', 'Netflix mensual', 45000, 'transferencia', 'banco', 'TXN-101', '2026-04-01', '2026-04-01 08:00:00')");
$db->exec("INSERT INTO expenses (user_id, category, description, amount, payment_method, cuenta, referencia, date, created_at) VALUES (1, 'Limpieza', 'Articulos de limpieza', 95000, 'efectivo', 'caja', '', '2026-04-08', '2026-04-08 09:00:00')");
$db->exec("INSERT INTO expenses (user_id, category, description, amount, payment_method, cuenta, referencia, date, created_at) VALUES (1, 'Transporte', 'Flete de mercaderia', 145000, 'transferencia', 'banco', 'TXN-102', '2026-04-12', '2026-04-12 10:00:00')");

// Actualizar balances clientes
$db->exec("UPDATE clients SET balance = 320000 WHERE id = 2");
$db->exec("UPDATE clients SET balance = 180000 WHERE id = 3");
$db->exec("UPDATE clients SET balance = 450000 WHERE id = 4");

echo "<p>✅ Datos de Abril 2026 insertados</p>";
echo "<hr><p style='color:green;'><strong>Datos de prueba creados correctamente!</strong></p>";
echo "<p><strong>Periodo:</strong> Marzo 2026 + Abril 2026</p>";
echo "<a href='?page=dashboard' class='btn btn-primary'>Ir al Dashboard</a>";