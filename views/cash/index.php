<?php 
$title = 'Caja';
$pageTitle = 'Control de Caja';

$cashRegister = db()->query("
    SELECT cr.*, u.name as user_name
    FROM cash_register cr
    LEFT JOIN users u ON cr.user_id = u.id
    WHERE cr.status = 'open'
    ORDER BY cr.id DESC
    LIMIT 1
")->fetch(PDO::FETCH_ASSOC);

$lastClosed = db()->query("
    SELECT cr.*, u.name as user_name
    FROM cash_register cr
    LEFT JOIN users u ON cr.user_id = u.id
    WHERE cr.status = 'closed'
    ORDER BY cr.id DESC
    LIMIT 1
")->fetch(PDO::FETCH_ASSOC);

$content = '
<div class="row mb-3">
    <div class="col-md-6">
        <a href="?page=home" class="btn btn-secondary">← Volver</a>
    </div>
</div>';

if (!$cashRegister) {
    $content .= '
    <div class="card mb-3">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Apertura de Caja</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="?page=cash&action=open">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Monto Inicial (Gs.)</label>
                            <input type="number" name="opening_amount" class="form-control" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Observaciones</label>
                            <textarea name="opening_notes" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Abrir Caja</button>
            </form>
        </div>
    </div>';
} else {
    $movements = db()->query("
        SELECT cm.*, u.name as user_name
        FROM cash_movements cm
        LEFT JOIN users u ON cm.user_id = u.id
        WHERE cm.cash_register_id = " . $cashRegister['id'] . "
        ORDER BY cm.id DESC
    ")->fetchAll(PDO::FETCH_ASSOC);
    
    $totalIn = array_sum(array_map(fn($m) => $m['type'] === 'in' ? $m['amount'] : 0, $movements));
    $totalOut = array_sum(array_map(fn($m) => $m['type'] === 'out' ? $m['amount'] : 0, $movements));
    $expected = $cashRegister['opening_amount'] + $totalIn - $totalOut;
    
    $content .= '
    <div class="row mb-3">
        <div class="col-md-12">
            <div class="alert alert-info">
                <strong>Caja Abierta</strong> - Usuario: ' . htmlspecialchars($cashRegister['user_name']) . ' | 
                Apertura: ' . Format::money($cashRegister['opening_amount']) . ' | 
                Esperado: ' . Format::money($expected) . '
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-4">
            <div class="card mb-3">
                <div class="card-body text-center">
                    <h3 class="text-success">' . Format::money($totalIn) . '</h3>
                    <p class="text-muted mb-0">Entradas</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card mb-3">
                <div class="card-body text-center">
                    <h3 class="text-danger">' . Format::money($totalOut) . '</h3>
                    <p class="text-muted mb-0">Salidas</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card mb-3">
                <div class="card-body text-center">
                    <h3>' . Format::money($expected) . '</h3>
                    <p class="text-muted mb-0">Esperado</p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="card mb-3">
        <div class="card-header d-flex justify-content-between">
            <h5 class="mb-0">Movimientos</h5>
            <div>
                <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#addMovementModal">+ Entrada</button>
                <button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#closeModal">Cerrar Caja</button>
            </div>
        </div>
        <div class="card-body p-0">
            <table class="table mb-0">
                <thead><tr><th>Hora</th><th>Tipo</th><th>Monto</th><th>Descripción</th><th>Usuario</th></tr></thead>
                <tbody>';
    
    foreach ($movements as $m) {
        $typeClass = $m['type'] === 'in' ? 'success' : 'danger';
        $content .= '<tr>
            <td>' . date('H:i', strtotime($m['created_at'])) . '</td>
            <td><span class="badge bg-' . $typeClass . '">' . ($m['type'] === 'in' ? 'Entrada' : 'Salida') . '</span></td>
            <td>' . Format::money($m['amount']) . '</td>
            <td>' . htmlspecialchars($m['description'] ?? '-') . '</td>
            <td>' . htmlspecialchars($m['user_name']) . '</td>
        </tr>';
    }
    
    $content .= '</tbody>
            </table>
        </div>
    </div>
    
    <div class="modal fade" id="addMovementModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Agregar Movimiento</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="?page=cash&action=movement">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Tipo</label>
                            <select name="type" class="form-select" required>
                                <option value="in">Entrada</option>
                                <option value="out">Salida</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Monto (Gs.)</label>
                            <input type="number" name="amount" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Descripción</label>
                            <input type="text" name="description" class="form-control">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="modal fade" id="closeModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Cerrar Caja</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="?page=cash&action=close">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Monto Final en Caja (Gs.)</label>
                            <input type="number" name="closing_amount" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Observaciones</label>
                            <textarea name="closing_notes" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-danger">Cerrar Caja</button>
                    </div>
                </form>
            </div>
        </div>
    </div>';
}

if ($lastClosed) {
    $content .= '
    <div class="card mt-3">
        <div class="card-header">
            <h5 class="mb-0">Último Cierre</h5>
        </div>
        <div class="card-body">
            <p><strong>Fecha:</strong> ' . Format::date($lastClosed['closed_at']) . '</p>
            <p><strong>Usuario:</strong> ' . htmlspecialchars($lastClosed['user_name']) . '</p>
            <p><strong>Apertura:</strong> ' . Format::money($lastClosed['opening_amount']) . '</p>
            <p><strong>Cierre:</strong> ' . Format::money($lastClosed['closing_amount']) . '</p>
            <p><strong>Diferencia:</strong> ' . Format::money($lastClosed['difference']) . '</p>
        </div>
    </div>';
}
