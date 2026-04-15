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
    
    $inCaja = 0;
    $inBanco = 0;
    $outCaja = 0;
    $outBanco = 0;
    foreach ($movements as $m) {
        if ($m['type'] === 'in') {
            if ($m['cuenta'] === 'caja') $inCaja += $m['amount'];
            else $inBanco += $m['amount'];
        } else {
            if ($m['cuenta'] === 'caja') $outCaja += $m['amount'];
            else $outBanco += $m['amount'];
        }
    }
    
    $content .= '
    <div class="row">
        <div class="col-md-3">
            <div class="card mb-3">
                <div class="card-body text-center position-relative">
                    <h4>' . Format::money($cashRegister['opening_amount']) . '</h4>
                    <p class="text-muted mb-0">Apertura</p>
                    <a href="?page=cash&action=edit_open&id=' . $cashRegister['id'] . '" class="position-absolute top-0 end-0 btn btn-sm btn-outline-secondary" style="font-size:10px;padding:2px 6px;">Editar</a>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card mb-3">
                <div class="card-body text-center">
                    <h4 class="text-success">' . Format::money($totalIn) . '</h4>
                    <p class="text-muted mb-0">+ Entradas</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card mb-3">
                <div class="card-body text-center">
                    <h4 class="text-danger">' . Format::money($totalOut) . '</h4>
                    <p class="text-muted mb-0">- Salidas</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card mb-3">
                <div class="card-body text-center">
                    <h4 class="text-primary">' . Format::money($expected) . '</h4>
                    <p class="text-muted mb-0">= Esperado</p>
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
                <thead><tr><th>Hora</th><th>Tipo</th><th>Método</th><th>Cuenta</th><th>Referencia</th><th>Monto</th><th>Descripción</th></tr></thead>
                <tbody>';
    
    foreach ($movements as $m) {
        $typeClass = $m['type'] === 'in' ? 'success' : 'danger';
        $methodLabel = $m['payment_method'] === 'efectivo' ? 'Efec.' : ($m['payment_method'] === 'transferencia' ? 'Transf.' : ($m['payment_method'] === 'qr' ? 'QR' : 'Tarj.'));
        $cuentaLabel = $m['cuenta'] === 'caja' ? 'Caja' : 'Banco';
        $content .= '<tr>
            <td>' . date('H:i', strtotime($m['created_at'])) . '</td>
            <td><span class="badge bg-' . $typeClass . '">' . ($m['type'] === 'in' ? 'Entrada' : 'Salida') . '</span></td>
            <td>' . $methodLabel . '</td>
            <td>' . $cuentaLabel . '</td>
            <td>' . htmlspecialchars($m['referencia'] ?? '-') . '</td>
            <td>' . Format::money($m['amount']) . '</td>
            <td>' . htmlspecialchars($m['description'] ?? '-') . '</td>
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
                        <div class="mb-3">
                            <label class="form-label">Método de Pago</label>
                            <select name="payment_method" class="form-select">
                                <option value="efectivo">Efectivo</option>
                                <option value="transferencia">Transferencia</option>
                                <option value="tarjeta">Tarjeta</option>
                                <option value="qr">QR</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Cuenta</label>
                            <select name="cuenta" class="form-select">
                                <option value="caja">Caja Física</option>
                                <option value="banco">Banco</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Referencia</label>
                            <input type="text" name="referencia" class="form-control">
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
                        <div class="row mb-3">
                            <div class="col">
                                <div class="alert alert-info mb-0">
                                    <strong>Esperado:</strong> ' . Format::money($expected) . '
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Monto Final en Caja (Gs.)</label>
                            <input type="number" name="closing_amount" id="closingAmount" class="form-control" value="' . $expected . '" required oninput="calcularDiferencia()">
                        </div>
                        <div class="mb-3">
                            <div class="alert" id="diferenciaAlert">
                                <strong>Diferencia:</strong> <span id="diferenciaDisplay">0</span>
                            </div>
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
    </div>
    <script>
    function calcularDiferencia() {
        var esperado = ' . $expected . ';
        var cierre = parseFloat(document.getElementById("closingAmount").value) || 0;
        var diff = cierre - esperado;
        var display = document.getElementById("diferenciaDisplay");
        var alert = document.getElementById("diferenciaAlert");
        
        display.textContent = diff.toLocaleString("es-PY");
        
        if (diff === 0) {
            alert.className = "alert alert-success mb-0";
        } else if (diff > 0) {
            alert.className = "alert alert-success mb-0";
            display.textContent = "+" + display.textContent + " (Sobró)";
        } else {
            alert.className = "alert alert-warning mb-0";
            display.textContent = display.textContent + " (Faltó)";
        }
    }
    calcularDiferencia();
    </script>';
}

// Historial de cajas cerradas
$closedRegisters = db()->query("
    SELECT cr.*, u.name as user_name
    FROM cash_register cr
    LEFT JOIN users u ON cr.user_id = u.id
    WHERE cr.status = 'closed'
    ORDER BY cr.closed_at DESC
    LIMIT 30
")->fetchAll(PDO::FETCH_ASSOC);

if ($closedRegisters) {
    $content .= '
    <div class="card mt-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Historial de Cajas</h5>
            <small class="text-muted">Click en una fila para ver movimientos</small>
        </div>
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Fecha</th>
                        <th>Usuario</th>
                        <th>Apertura</th>
                        <th>Entradas</th>
                        <th>Salidas</th>
                        <th>Esperado</th>
                        <th>Cierre</th>
                        <th>Diferencia</th>
                    </tr>
                </thead>
                <tbody>';
    
    foreach ($closedRegisters as $cr) {
        $movs = db()->query("SELECT SUM(CASE WHEN type = 'in' THEN amount ELSE 0 END) as tin, SUM(CASE WHEN type = 'out' THEN amount ELSE 0 END) as tout FROM cash_movements WHERE cash_register_id = " . $cr['id'])->fetch(PDO::FETCH_ASSOC);
        $tin = $movs['tin'] ?? 0;
        $tout = $movs['tout'] ?? 0;
        $exp = $cr['opening_amount'] + $tin - $tout;
        $diffCalc = ($cr['closing_amount'] ?? 0) - $cr['opening_amount'];
        $diffClass = $diffCalc > 0 ? 'text-success' : ($diffCalc < 0 ? 'text-danger' : '');
        
        $content .= '<tr style="cursor:pointer" onclick="window.location.href=\'?page=cash&action=view&id=' . $cr['id'] . '\'">
            <td>' . Format::date($cr['opened_at']) . '</td>
            <td>' . htmlspecialchars($cr['user_name']) . '</td>
            <td>' . Format::money($cr['opening_amount']) . '</td>
            <td class="text-success">' . Format::money($tin) . '</td>
            <td class="text-danger">' . Format::money($tout) . '</td>
            <td>' . Format::money($exp) . '</td>
            <td>' . Format::money($cr['closing_amount'] ?? 0) . '</td>
            <td class="' . $diffClass . '"><strong>' . Format::money($diffCalc) . '</strong></td>
        </tr>';
    }
    
    $content .= '</tbody>
            </table>
        </div>
    </div>';
}
