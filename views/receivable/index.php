<?php 
$title = 'Cuentas por Cobrar';
$pageTitle = 'Cuentas por Cobrar';

$action = $_GET['action'] ?? 'list';
$client_id = $_GET['client'] ?? null;
$filter = $_GET['filter'] ?? null;

if ($action === 'pay' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $account_id = $_POST['account_id'] ?? null;
    $amount = floatval($_POST['amount'] ?? 0);
    $payment_method = $_POST['payment_method'] ?? 'efectivo';
    $reference = trim($_POST['reference'] ?? '');
    $notes = trim($_POST['notes'] ?? '');
    
    try {
        db()->beginTransaction();
        
        $account = db()->prepare("SELECT * FROM accounts_receivable WHERE id = ?");
        $account->execute([$account_id]);
        $accountData = $account->fetch(PDO::FETCH_ASSOC);
        
        if (!$accountData) {
            throw new Exception("Cuenta por cobrar no encontrada");
        }
        
        $newPaid = $accountData['paid_amount'] + $amount;
        $newStatus = $newPaid >= $accountData['amount'] ? 'cancelada' : 'parcial';
        
        $stmt = db()->prepare("UPDATE accounts_receivable SET paid_amount = ?, status = ? WHERE id = ?");
        $stmt->execute([$newPaid, $newStatus, $account_id]);
        
        $stmt = db()->prepare("INSERT INTO payments (account_receivable_id, user_id, amount, payment_method, reference, notes) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$account_id, auth(), $amount, $payment_method, $reference, $notes]);
        
        if ($newPaid >= $accountData['amount']) {
            $stmt = db()->prepare("UPDATE clients SET balance = balance - ? WHERE id = ?");
            $stmt->execute([$accountData['amount'], $accountData['client_id']]);
            
            $stmt = db()->prepare("UPDATE sales SET status = 'pagada' WHERE id = ?");
            $stmt->execute([$accountData['sale_id']]);
        }
        
        db()->commit();
        flash('success', 'Cobro registrado correctamente');
        
    } catch (Exception $e) {
        db()->rollBack();
        flash('error', 'Error: ' . $e->getMessage());
    }
    redirect('?page=receivable');
}

$accounts = db()->query("
    SELECT ar.*, c.name as client_name, c.phone as client_phone, s.id as sale_id
    FROM accounts_receivable ar
    JOIN clients c ON ar.client_id = c.id
    LEFT JOIN sales s ON ar.sale_id = s.id
    WHERE ar.status != 'cancelada'
    ORDER BY ar.due_date ASC
")->fetchAll(PDO::FETCH_ASSOC);

if ($client_id) {
    $accounts = array_filter($accounts, fn($a) => $a['client_id'] == $client_id);
}

if ($filter === '10days') {
    $today = new DateTime();
    $accounts = array_filter($accounts, function($a) use ($today) {
        $dueDate = new DateTime($a['due_date']);
        $days = $today->diff($dueDate)->days;
        return $days >= 0 && $days <= 10;
    });
}

$pageTitle = $filter === '10days' ? 'Cuentas por Cobrar (Próximos 10 días)' : 'Cuentas por Cobrar';

$content = '
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Cuentas Pendientes</h5>
    </div>
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Cliente</th>
                    <th>Factura</th>
                    <th>Monto Total</th>
                    <th>Pagado</th>
                    <th>Saldo</th>
                    <th>Vencimiento</th>
                    <th>Dias</th>
                    <th>Accion</th>
                </tr>
            </thead>
            <tbody>';

foreach ($accounts as $a) {
    $pending = $a['amount'] - $a['paid_amount'];
    $dueDate = new DateTime($a['due_date']);
    $today = new DateTime();
    $daysUntilDue = $today->diff($dueDate)->days;
    $isOverdue = $dueDate < $today;
    
    $daysClass = $isOverdue ? 'text-danger fw-bold' : ($daysUntilDue <= 3 ? 'text-warning fw-bold' : 'text-muted');
    $daysText = $isOverdue ? '-' . $daysUntilDue . ' dias' : ($daysUntilDue == 0 ? 'Hoy' : $daysUntilDue . ' dias');
    $dueDateClass = $isOverdue ? 'text-danger' : ($daysUntilDue <= 3 ? 'text-warning' : '');
    
    $content .= '<tr>
        <td><strong>' . htmlspecialchars($a['client_name']) . '</strong></td>
        <td><a href="?page=sales&action=edit&id=' . $a['sale_id'] . '">#' . $a['sale_id'] . '</a></td>
        <td>' . Format::money($a['amount']) . '</td>
        <td>' . Format::money($a['paid_amount']) . '</td>
        <td class="text-danger fw-bold">' . Format::money($pending) . '</td>
        <td class="' . $dueDateClass . '"><strong>' . Format::date($a['due_date']) . '</strong></td>
        <td class="' . $daysClass . '">' . $daysText . '</td>
        <td>
            <button class="btn btn-sm btn-success" onclick="showPayModal(' . $a['id'] . ', ' . $pending . ', \'' . htmlspecialchars($a['client_name']) . '\')">
                <i class="bi bi-cash"></i> Cobrar
            </button>
        </td>
    </tr>';
}

$content .= '</tbody>
        </table>
    </div>
</div>';

$totalPendiente = 0;
foreach ($accounts as $a) {
    $totalPendiente += ($a['amount'] - $a['paid_amount']);
}
$content .= '
<div class="alert alert-info mt-3">
    <strong>Total pendiente: ' . Format::money($totalPendiente) . '</strong>
</div>';

$content .= '
<div class="modal fade" id="payModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Registrar Cobro</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="?page=receivable&action=pay">
                <div class="modal-body">
                    <input type="hidden" name="account_id" id="payAccountId">
                    <div class="alert alert-info">
                        <strong>Cliente:</strong> <span id="payClientName"></span><br>
                        <strong>Saldo pendiente:</strong> <span id="payPending"></span>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Monto a pagar *</label>
                        <input type="number" name="amount" id="payAmount" class="form-control" step="1" min="1" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Metodo de pago</label>
                        <select name="payment_method" class="form-select">
                            <option value="efectivo">Efectivo</option>
                            <option value="tarjeta">Tarjeta</option>
                            <option value="transferencia">Transferencia</option>
                            <option value="cheque">Cheque</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Referencia / Nro. comprobante</label>
                        <input type="text" name="reference" class="form-control" placeholder="Ej: Nro. de transferencia, cheque, etc.">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notas</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="Observaciones adicionales"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success">Registrar Pago</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function formatMoney(num) {
    return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
}

function showPayModal(accountId, amount, clientName) {
    document.getElementById("payAccountId").value = accountId;
    document.getElementById("payClientName").textContent = clientName;
    document.getElementById("payPending").textContent = "Gs. " + formatMoney(amount);
    document.getElementById("payAmount").value = Math.round(amount);
    new bootstrap.Modal(document.getElementById("payModal")).show();
}
</script>';
