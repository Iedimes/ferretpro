<?php 
$title = 'Cuentas por Cobrar';
$pageTitle = 'Cuentas por Cobrar';

$action = $_GET['action'] ?? 'list';
$client_id = $_GET['client'] ?? null;
$filter = $_GET['filter'] ?? null;

if ($client_id) {
    $accounts = array_filter($accounts, fn($a) => $a['client_id'] == $client_id);
}

if ($filter === '10days') {
    $pageTitle = 'Cuentas por Cobrar (Próximos 10 días)';
} elseif ($filter === 'overdue') {
    $pageTitle = 'Cuentas por Cobrar Vencidas';
}

$totalPendiente = 0;
foreach ($accounts as $a) {
    $totalPendiente += ($a['amount'] - $a['paid_amount']);
}

$content = '
<div class="mb-3">
    <a href="?page=dashboard" class="btn btn-nav-back me-2"><i class="bi bi-arrow-left"></i> Volver al Dashboard</a>
</div>

<h4 class="mb-4"><i class="bi bi-file-earmark-text"></i> ' . htmlspecialchars($pageTitle) . '</h4>

<div class="row mb-4">
    <div class="col-md-6">
        <div class="card" style="border-top: 5px solid var(--danger); background: linear-gradient(135deg, rgba(239, 68, 68, 0.08), transparent);">
            <div class="card-body">
                <h6 class="text-danger mb-1"><i class="bi bi-exclamation-circle"></i> Total Pendiente de Cobrar</h6>
                <h3 class="mb-0 text-danger" style="font-size: 2rem; font-weight: 700;">' . Format::money($totalPendiente) . '</h3>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card" style="border-top: 5px solid var(--info); background: linear-gradient(135deg, rgba(6, 182, 212, 0.08), transparent);">
            <div class="card-body">
                <h6 class="text-info mb-1"><i class="bi bi-list-check"></i> Cuentas Pendientes</h6>
                <h3 class="mb-0 text-info" style="font-size: 2rem; font-weight: 700;">' . count($accounts) . '</h3>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header bg-light">
        <h6 class="mb-0"><i class="bi bi-table"></i> Detalle de Cuentas</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Cliente</th>
                        <th>Factura</th>
                        <th>Monto Total</th>
                        <th>Pagado</th>
                        <th>Saldo</th>
                        <th>Vencimiento</th>
                        <th>Días</th>
                        <th>Acción</th>
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
        <td><a href="?page=sales&action=edit&id=' . $a['sale_id'] . '" class="badge bg-primary text-decoration-none" style="font-size: 0.9rem;">#' . $a['sale_id'] . '</a></td>
        <td><strong>' . Format::money($a['amount']) . '</strong></td>
        <td><span class="badge bg-success" style="font-size: 0.85rem;">' . Format::money($a['paid_amount']) . '</span></td>
        <td><span class="badge bg-danger" style="font-size: 0.85rem;">' . Format::money($pending) . '</span></td>
        <td class="' . $dueDateClass . '"><strong>' . Format::date($a['due_date']) . '</strong></td>
        <td class="' . $daysClass . '"><strong>' . $daysText . '</strong></td>
        <td>
            <button class="btn btn-sm btn-success" onclick="showPayModal(' . $a['id'] . ', ' . $pending . ', \'' . htmlspecialchars($a['client_name']) . '\')">
                <i class="bi bi-cash-circle"></i> Cobrar
            </button>
        </td>
    </tr>';
}

$content .= '</tbody>
                </table>
            </div>
    </div>
</div>

<div class="modal fade" id="payModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h5 class="modal-title"><i class="bi bi-cash-circle"></i> Registrar Cobro</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="?page=receivable&action=pay">
                <div class="modal-body">
                    <input type="hidden" name="account_id" id="payAccountId">
                    <div class="alert alert-info mb-3">
                        <strong><i class="bi bi-info-circle"></i> Cliente:</strong> <span id="payClientName"></span><br>
                        <strong>Saldo pendiente:</strong> <span id="payPending" style="color: var(--danger); font-weight: 700;"></span>
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><i class="bi bi-cash"></i> Monto a pagar *</label>
                        <input type="number" name="amount" id="payAmount" class="form-control" step="1" min="1" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><i class="bi bi-credit-card"></i> Método de pago</label>
                        <select name="payment_method" class="form-select" required>
                            <option value="efectivo">Efectivo</option>
                            <option value="tarjeta">Tarjeta</option>
                            <option value="transferencia">Transferencia</option>
                            <option value="cheque">Cheque</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><i class="bi bi-receipt"></i> Referencia / Nro. comprobante</label>
                        <input type="text" name="reference" class="form-control" placeholder="Ej: Nro. de transferencia, cheque, etc.">
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><i class="bi bi-chat-left-text"></i> Notas</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="Observaciones adicionales"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="bi bi-x"></i> Cancelar</button>
                    <button type="submit" class="btn btn-success"><i class="bi bi-check"></i> Registrar Pago</button>
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
    document.getElementById("payPending").textContent = "Gs. " + formatMoney(Math.round(amount));
    document.getElementById("payAmount").value = Math.round(amount);
    new bootstrap.Modal(document.getElementById("payModal")).show();
}
</script>';
