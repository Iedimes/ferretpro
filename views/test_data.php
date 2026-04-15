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

// Ventas Marzo (~3M por dia)
$db->exec("INSERT INTO sales (user_id, client_id, type, payment_method, subtotal, discount, total, delivery_type, status, created_at) VALUES (1, 2, 'contado', 'efectivo', 2500000, 0, 2500000, 'mostrador', 'pagada', '2026-03-01 09:00:00')");
$db->exec("INSERT INTO sales (user_id, client_id, type, payment_method, subtotal, discount, total, delivery_type, status, created_at) VALUES (1, 3, 'contado', 'transferencia', 3200000, 0, 3200000, 'mostrador', 'pagada', '2026-03-02 10:30:00')");
$db->exec("INSERT INTO sales (user_id, client_id, type, payment_method, subtotal, discount, total, delivery_type, status, created_at) VALUES (1, 4, 'contado', 'qr', 2800000, 0, 2800000, 'mostrador', 'pagada', '2026-03-03 14:00:00')");
$db->exec("INSERT INTO sales (user_id, client_id, type, payment_method, subtotal, discount, total, delivery_type, status, created_at) VALUES (1, 2, 'contado', 'efectivo', 1850000, 0, 1850000, 'mostrador', 'pagada', '2026-03-05 11:00:00')");
$db->exec("INSERT INTO sales (user_id, client_id, type, payment_method, subtotal, discount, total, delivery_type, status, created_at) VALUES (1, NULL, 'contado', 'efectivo', 3200000, 0, 3200000, 'mostrador', 'pagada', '2026-03-07 16:00:00')");
$db->exec("INSERT INTO sales (user_id, client_id, type, payment_method, subtotal, discount, total, delivery_type, status, created_at) VALUES (1, 3, 'credito', 'efectivo', 4500000, 0, 4500000, 'mostrador', 'pendiente', '2026-03-10 10:00:00')");
$db->exec("INSERT INTO sales (user_id, client_id, type, payment_method, subtotal, discount, total, delivery_type, status, created_at) VALUES (1, 4, 'contado', 'tarjeta', 3500000, 0, 3500000, 'mostrador', 'pagada', '2026-03-12 15:00:00')");
$db->exec("INSERT INTO sales (user_id, client_id, type, payment_method, subtotal, discount, total, delivery_type, status, created_at) VALUES (1, 2, 'contado', 'transferencia', 2800000, 0, 2800000, 'mostrador', 'pagada', '2026-03-15 09:30:00')");
$db->exec("INSERT INTO sales (user_id, client_id, type, payment_method, subtotal, discount, total, delivery_type, status, created_at) VALUES (1, NULL, 'contado', 'efectivo', 1950000, 0, 1950000, 'mostrador', 'pagada', '2026-03-18 14:00:00')");
$db->exec("INSERT INTO sales (user_id, client_id, type, payment_method, subtotal, discount, total, delivery_type, status, created_at) VALUES (1, 3, 'contado', 'qr', 3100000, 0, 3100000, 'mostrador', 'pagada', '2026-03-20 11:00:00')");
$db->exec("INSERT INTO sales (user_id, client_id, type, payment_method, subtotal, discount, total, delivery_type, status, created_at) VALUES (1, 4, 'contado', 'efectivo', 4200000, 0, 4200000, 'mostrador', 'pagada', '2026-03-22 16:30:00')");
$db->exec("INSERT INTO sales (user_id, client_id, type, payment_method, subtotal, discount, total, delivery_type, status, created_at) VALUES (1, 2, 'contado', 'transferencia', 2550000, 0, 2550000, 'mostrador', 'pagada', '2026-03-25 10:00:00')");
$db->exec("INSERT INTO sales (user_id, client_id, type, payment_method, subtotal, discount, total, delivery_type, status, created_at) VALUES (1, NULL, 'contado', 'efectivo', 2850000, 0, 2850000, 'mostrador', 'pagada', '2026-03-28 15:00:00')");
$db->exec("INSERT INTO sales (user_id, client_id, type, payment_method, subtotal, discount, total, delivery_type, status, created_at) VALUES (1, 3, 'contado', 'tarjeta', 3800000, 0, 3800000, 'mostrador', 'pagada', '2026-03-31 14:00:00')");

// CxC de Marzo (una pendiente)
$db->exec("INSERT INTO accounts_receivable (client_id, sale_id, amount, due_date, status, created_at) VALUES (3, 6, 4500000, '2026-04-10', 'pendiente', '2026-03-10 10:00:00')");

// Compras Marzo
$db->exec("INSERT INTO purchases (provider_id, user_id, invoice_number, subtotal, discount, total, payment_method, status, created_at) VALUES (1, 1, 'F001-001', 8500000, 0, 8500000, 'credito', 'pending', '2026-03-05 10:00:00')");
$db->exec("INSERT INTO purchases (provider_id, user_id, invoice_number, subtotal, discount, total, payment_method, status, created_at) VALUES (2, 1, 'F001-002', 4200000, 0, 4200000, 'credito', 'pending', '2026-03-15 10:00:00')");

// CxP de Marzo (vencidas)
$db->exec("INSERT INTO accounts_payable (provider_id, purchase_id, amount, due_date, status, created_at) VALUES (1, 1, 8500000, '2026-04-01', 'pendiente', '2026-03-05 10:00:00')");
$db->exec("INSERT INTO accounts_payable (provider_id, purchase_id, amount, due_date, status, created_at) VALUES (2, 2, 4200000, '2026-04-10', 'pendiente', '2026-03-15 10:00:00')");

// Cotizaciones Marzo
$db->exec("INSERT INTO quotes (client_id, client_name, client_document, user_id, validity_days, discount, subtotal, total, status, created_at, expires_at) VALUES (2, 'Juan Gomez', '20123456789', 1, 30, 0, 5200000, 5200000, 'pending', '2026-03-01', '2026-03-31')");
$db->exec("INSERT INTO quotes (client_id, client_name, client_document, user_id, validity_days, discount, subtotal, total, status, created_at, expires_at) VALUES (3, 'Maria Lopez', '27123456789', 1, 15, 250000, 8500000, 8250000, 'pending', '2026-03-10', '2026-03-25')");
$db->exec("INSERT INTO quotes (client_id, client_name, client_document, user_id, validity_days, discount, subtotal, total, status, created_at, expires_at) VALUES (4, 'Ferreteria El Tornillo', '30123456789', 1, 30, 0, 12500000, 12500000, 'pending', '2026-03-20', '2026-04-19')");

// Notas de credito Marzo (varias)
$db->exec("INSERT INTO credit_notes (sale_id, user_id, client_id, reason, subtotal, total, status, created_at) VALUES (1, 1, 2, 'producto defectuoso', 250000, 250000, 'pending', '2026-03-05')");
$db->exec("INSERT INTO credit_notes (sale_id, user_id, client_id, reason, subtotal, total, status, created_at) VALUES (3, 1, 3, 'producto incorrecto', 180000, 180000, 'pending', '2026-03-15')");
$db->exec("INSERT INTO credit_notes (sale_id, user_id, client_id, reason, subtotal, total, status, created_at) VALUES (7, 1, 4, 'no lo necesitaba', 350000, 350000, 'pending', '2026-03-25')");

// Gastos Marzo
$db->exec("INSERT INTO expenses (user_id, category, description, amount, payment_method, cuenta, referencia, date, created_at) VALUES (1, 'Utiles', 'Papel, lapiceras, carpetas', 350000, 'efectivo', 'caja', '', '2026-03-01', '2026-03-01 08:00:00')");
$db->exec("INSERT INTO expenses (user_id, category, description, amount, payment_method, cuenta, referencia, date, created_at) VALUES (1, 'Servicios', 'Internet mensual', 185000, 'transferencia', 'banco', 'TXN-001', '2026-03-01', '2026-03-01 08:00:00')");
$db->exec("INSERT INTO expenses (user_id, category, description, amount, payment_method, cuenta, referencia, date, created_at) VALUES (1, 'Mantenimiento', 'Reparacion de equipos', 450000, 'efectivo', 'caja', '', '2026-03-10', '2026-03-10 09:00:00')");
$db->exec("INSERT INTO expenses (user_id, category, description, amount, payment_method, cuenta, referencia, date, created_at) VALUES (1, 'Transporte', 'Flete de mercaderia', 520000, 'transferencia', 'banco', 'TXN-002', '2026-03-20', '2026-03-20 10:00:00')");
$db->exec("INSERT INTO expenses (user_id, category, description, amount, payment_method, cuenta, referencia, date, created_at) VALUES (1, 'Limpieza', 'Articulos de limpieza', 280000, 'efectivo', 'caja', '', '2026-03-28', '2026-03-28 08:00:00')");

echo "<p>✅ Datos de Marzo 2026 insertados</p>";

// ========== ABRIL 2026 (Mes actual) ==========
echo "<p>Generando datos de Abril 2026...</p>";

// Ventas Abril (~3M por dia)
$db->exec("INSERT INTO sales (user_id, client_id, type, payment_method, subtotal, discount, total, delivery_type, status, created_at) VALUES (1, 2, 'contado', 'efectivo', 3500000, 0, 3500000, 'mostrador', 'pagada', '2026-04-01 09:00:00')");
$db->exec("INSERT INTO sales (user_id, client_id, type, payment_method, subtotal, discount, total, delivery_type, status, created_at) VALUES (1, 3, 'contado', 'transferencia', 4200000, 0, 4200000, 'mostrador', 'pagada', '2026-04-02 10:30:00')");
$db->exec("INSERT INTO sales (user_id, client_id, type, payment_method, subtotal, discount, total, delivery_type, status, created_at) VALUES (1, 4, 'contado', 'qr', 2850000, 0, 2850000, 'mostrador', 'pagada', '2026-04-03 14:00:00')");
$db->exec("INSERT INTO sales (user_id, client_id, type, payment_method, subtotal, discount, total, delivery_type, status, created_at) VALUES (1, 2, 'contado', 'tarjeta', 3200000, 0, 3200000, 'mostrador', 'pagada', '2026-04-05 11:00:00')");
$db->exec("INSERT INTO sales (user_id, client_id, type, payment_method, subtotal, discount, total, delivery_type, status, created_at) VALUES (1, NULL, 'contado', 'efectivo', 2950000, 0, 2950000, 'mostrador', 'pagada', '2026-04-07 16:00:00')");
$db->exec("INSERT INTO sales (user_id, client_id, type, payment_method, subtotal, discount, total, delivery_type, status, created_at) VALUES (1, 3, 'credito', 'efectivo', 5500000, 0, 5500000, 'mostrador', 'pendiente', '2026-04-08 10:00:00')");
$db->exec("INSERT INTO sales (user_id, client_id, type, payment_method, subtotal, discount, total, delivery_type, status, created_at) VALUES (1, 4, 'contado', 'transferencia', 3800000, 0, 3800000, 'mostrador', 'pagada', '2026-04-10 15:00:00')");
$db->exec("INSERT INTO sales (user_id, client_id, type, payment_method, subtotal, discount, total, delivery_type, status, created_at) VALUES (1, 2, 'contado', 'qr', 2650000, 0, 2650000, 'mostrador', 'pagada', '2026-04-12 09:30:00')");
$db->exec("INSERT INTO sales (user_id, client_id, type, payment_method, subtotal, discount, total, delivery_type, status, created_at) VALUES (1, NULL, 'contado', 'efectivo', 3200000, 0, 3200000, 'mostrador', 'pagada', '2026-04-14 14:00:00')");
$db->exec("INSERT INTO sales (user_id, client_id, type, payment_method, subtotal, discount, total, delivery_type, status, created_at) VALUES (1, 3, 'contado', 'tarjeta', 4100000, 0, 4100000, 'mostrador', 'pagada', '2026-04-15 10:00:00')");

// CxC Abril
$db->exec("INSERT INTO accounts_receivable (client_id, sale_id, amount, due_date, status, created_at) VALUES (3, 20, 5500000, '2026-04-20', 'pendiente', '2026-04-08 10:00:00')");

// Cotizaciones Abril
$db->exec("INSERT INTO quotes (client_id, client_name, client_document, user_id, validity_days, discount, subtotal, total, status, created_at, expires_at) VALUES (2, 'Juan Gomez', '20123456789', 1, 30, 0, 6200000, 6200000, 'pending', '2026-04-05', '2026-05-05')");
$db->exec("INSERT INTO quotes (client_id, client_name, client_document, user_id, validity_days, discount, subtotal, total, status, created_at, expires_at) VALUES (3, 'Maria Lopez', '27123456789', 1, 15, 350000, 9200000, 8850000, 'pending', '2026-04-10', '2026-04-25')");
$db->exec("INSERT INTO quotes (client_id, client_name, client_document, user_id, validity_days, discount, subtotal, total, status, created_at, expires_at) VALUES (4, 'Ferreteria El Tornillo', '30123456789', 1, 30, 0, 15200000, 15200000, 'pending', '2026-04-12', '2026-05-12')");
$db->exec("INSERT INTO quotes (client_id, client_name, client_document, user_id, validity_days, discount, subtotal, total, status, created_at, expires_at) VALUES (2, 'Juan Gomez', '20123456789', 1, 30, 0, 4800000, 4800000, 'pending', '2026-04-14', '2026-05-14')");

// Notas de credito Abril
$db->exec("INSERT INTO credit_notes (sale_id, user_id, client_id, reason, subtotal, total, status, created_at) VALUES (15, 1, 2, 'producto defectuoso', 350000, 350000, 'pending', '2026-04-12')");
$db->exec("INSERT INTO credit_notes (sale_id, user_id, client_id, reason, subtotal, total, status, created_at) VALUES (17, 1, 3, 'producto incorrecto', 280000, 280000, 'pending', '2026-04-13')");
$db->exec("INSERT INTO credit_notes (sale_id, user_id, client_id, reason, subtotal, total, status, created_at) VALUES (19, 1, 4, 'no lo necesitaba', 420000, 420000, 'pending', '2026-04-14')");

// Gastos Abril
$db->exec("INSERT INTO expenses (user_id, category, description, amount, payment_method, cuenta, referencia, date, created_at) VALUES (1, 'Servicios', 'Netflix mensual', 65000, 'transferencia', 'banco', 'TXN-101', '2026-04-01', '2026-04-01 08:00:00')");
$db->exec("INSERT INTO expenses (user_id, category, description, amount, payment_method, cuenta, referencia, date, created_at) VALUES (1, 'Limpieza', 'Articulos de limpieza', 320000, 'efectivo', 'caja', '', '2026-04-08', '2026-04-08 09:00:00')");
$db->exec("INSERT INTO expenses (user_id, category, description, amount, payment_method, cuenta, referencia, date, created_at) VALUES (1, 'Transporte', 'Flete de mercaderia', 480000, 'transferencia', 'banco', 'TXN-102', '2026-04-12', '2026-04-12 10:00:00')");
$db->exec("INSERT INTO expenses (user_id, category, description, amount, payment_method, cuenta, referencia, date, created_at) VALUES (1, 'Utiles', 'Utiles de oficina', 185000, 'efectivo', 'caja', '', '2026-04-14', '2026-04-14 08:00:00')");

// Actualizar balances clientes
$db->exec("UPDATE clients SET balance = 3200000 WHERE id = 2");
$db->exec("UPDATE clients SET balance = 1800000 WHERE id = 3");
$db->exec("UPDATE clients SET balance = 4500000 WHERE id = 4");

echo "<p>✅ Datos de Abril 2026 insertados</p>";
echo "<hr><p style='color:green;'><strong>Datos de prueba creados correctamente!</strong></p>";
echo "<p><strong>Periodo:</strong> Marzo + Abril 2026</p>";
echo "<p><strong>Ventas promedio:</strong> ~3M por dia</p>";
echo "<a href='?page=dashboard' class='btn btn-primary'>Ir al Dashboard</a>";