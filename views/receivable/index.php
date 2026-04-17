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

$content = '
<div class="mb-3">
    <a href="?page=dashboard" class="btn btn-nav-back me-2"><i class="bi bi-arrow-left"></i> Volver al Dashboard</a>
</div>

<div class="card mb-4">
    <div class="card-body" style="background: linear-gradient(135deg, rgba(239, 68, 68, 0.1), transparent);">
        <h6 class="text-muted mb-1"><i class="bi bi-info-circle"></i> Total Pendiente</h6>
        <h3 class="mb-0" style="font-size: 2rem; font-weight: 700; color: var(--danger);" id="totalPendienteBadge">Cargando...</h3>
    </div>
</div>

<div class="card">
    <div class="card-header bg-light d-flex justify-content-between align-items-center">
        <h6 class="mb-0"><i class="bi bi-file-earmark-text"></i> ' . htmlspecialchars($pageTitle) . '</h6>
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
        <td><a href="?page=sales&action=edit&id=' . $a['sale_id'] . '" class="badge bg-primary text-decoration-none">#' . $a['sale_id'] . '</a></td>
        <td><strong>' . Format::money($a['amount']) . '</strong></td>
        <td><span class="badge bg-success">' . Format::money($a['paid_amount']) . '</span></td>
        <td><span class="badge bg-danger">' . Format::money($pending) . '</span></td>
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
</div>';

$totalPendiente = 0;
foreach ($accounts as $a) {
    $totalPendiente += ($a['amount'] - $a['paid_amount']);
}

$content .= '</tbody>
        </table>
    </div>
</div>

<div class="card mt-3" style="border-top: 5px solid var(--danger);">
    <div class="card-body" style="background: linear-gradient(135deg, rgba(239, 68, 68, 0.08), transparent);">
        <h6 class="text-danger mb-1"><i class="bi bi-exclamation-circle"></i> Total Pendiente de Cobrar</h6>
        <h3 class="mb-0 text-danger" style="font-size: 2rem; font-weight: 700;">' . Format::money($totalPendiente) . '</h3>
    </div>
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
