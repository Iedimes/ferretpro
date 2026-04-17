<?php 
$title = 'Cuentas por Pagar';
$pageTitle = 'Cuentas por Pagar';

$filter = $_GET['filter'] ?? null;

$accounts = db()->query("SELECT ap.*, p.name as provider_name FROM accounts_payable ap JOIN providers p ON ap.provider_id = p.id WHERE ap.status != 'cancelada' ORDER BY ap.due_date ASC")->fetchAll(PDO::FETCH_ASSOC);

if ($filter === '10days' && !empty($accounts)) {
    $today = new DateTime();
    $accounts = array_filter($accounts, function($a) use ($today) {
        $dueDate = new DateTime($a['due_date']);
        $days = $today->diff($dueDate)->days;
        return $days >= 0 && $days <= 10;
    });
    $pageTitle = 'Cuentas por Pagar (Próximos 10 días)';
}

$content = '
<div class="row mb-3">
    <div class="col-md-6">
        <a href="?page=home" class="btn btn-secondary">← Volver</a>
    </div>
</div>';

$content .= '
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Cuentas por Pagar</h5>
    </div>
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <th>Proveedor</th>
                    <th>Monto</th>
                    <th>Vencimiento</th>
                    <th>Días</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>';

if (count($accounts) == 0) {
    $content .= '<tr><td colspan="7" class="text-center">No hay cuentas por pagar</td></tr>';
} else {
    foreach ($accounts as $ac) {
        $statusClass = $ac['status'] === 'pendiente' ? 'warning' : 'success';
        $statusLabel = $ac['status'] === 'pendiente' ? 'Pendiente' : 'Pagado';
        
        $dueDate = new DateTime($ac['due_date']);
        $today = new DateTime();
        $daysUntilDue = $today->diff($dueDate)->days;
        $isOverdue = $dueDate < $today;
        
        $daysClass = $isOverdue ? 'text-danger fw-bold' : ($daysUntilDue <= 7 ? 'text-warning fw-bold' : 'text-muted');
        $daysText = $isOverdue ? '-' . $daysUntilDue . ' dias' : ($daysUntilDue == 0 ? 'Hoy' : $daysUntilDue . ' dias');
        $dueDateClass = $isOverdue ? 'text-danger' : ($daysUntilDue <= 7 ? 'text-warning' : '');
        
        $content .= '<tr>
            <td>#' . $ac['id'] . '</td>
            <td>' . htmlspecialchars($ac['provider_name']) . '</td>
            <td>' . Format::money($ac['amount']) . '</td>
            <td class="' . $dueDateClass . '"><strong>' . Format::date($ac['due_date']) . '</strong></td>
            <td class="' . $daysClass . '">' . $daysText . '</td>
            <td><span class="badge bg-' . $statusClass . '">' . $statusLabel . '</span></td>
            <td>' . ($ac['status'] === 'pendiente' ? '<button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#payModal' . $ac['id'] . '">Pagar</button>' : '-') . '</td>
        </tr>';
        
        if ($ac['status'] === 'pendiente') {
            $content .= '
    <div class="modal fade" id="payModal' . $ac['id'] . '" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Pagar Cuenta #' . $ac['id'] . '</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="?page=payable&action=pay&id=' . $ac['id'] . '">
                    <div class="modal-body">
                        <p><strong>Proveedor:</strong> ' . htmlspecialchars($ac['provider_name']) . '</p>
                        <p><strong>Monto:</strong> ' . Format::money($ac['amount']) . '</p>
                        <hr>
                        <div class="mb-3">
                            <label class="form-label">Método de Pago</label>
                            <select name="payment_method" class="form-select" required>
                                <option value="efectivo">Efectivo</option>
                                <option value="transferencia">Transferencia</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Cuenta de Origen</label>
                            <select name="cuenta" class="form-select" required>
                                <option value="caja">Caja Física</option>
                                <option value="banco">Banco</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Banco Origen</label>
                            <input type="text" name="banco_origen" class="form-control" placeholder="Ej: Banco Continental">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Banco Destino</label>
                            <input type="text" name="banco_destino" class="form-control" placeholder="Ej: Banco del Paraguay">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">N° Cuenta Destino</label>
                            <input type="text" name="cuenta_destino" class="form-control" placeholder="N° de cuenta">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">N° de Transacción</label>
                            <input type="text" name="referencia" class="form-control" placeholder="N° de operación">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Notas</label>
                            <textarea name="notes" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success">Confirmar Pago</button>
                    </div>
                </form>
            </div>
        </div>
    </div>';
        }
    }
}

$content .= '</tbody>
        </table>
    </div>
</div>';

if (count($accounts) > 0) {
    $totalPendiente = array_sum(array_map(fn($a) => $a['status'] === 'pendiente' ? $a['amount'] : 0, $accounts));
    $content .= '
<div class="alert alert-info mt-3">
    <strong>Total pendiente: ' . Format::money($totalPendiente) . '</strong>
</div>';
}