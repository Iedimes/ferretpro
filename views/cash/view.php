<?php 
$id = intval($_GET['id'] ?? 0);
$cashRegister = db()->query("
    SELECT cr.*, u.name as user_name
    FROM cash_register cr
    LEFT JOIN users u ON cr.user_id = u.id
    WHERE cr.id = $id
")->fetch(PDO::FETCH_ASSOC);

if (!$cashRegister) {
    Flash::error('Caja no encontrada');
    header('Location: ?page=cash');
    exit;
}

$movements = db()->query("
    SELECT cm.*, u.name as user_name
    FROM cash_movements cm
    LEFT JOIN users u ON cm.user_id = u.id
    WHERE cm.cash_register_id = $id
    ORDER BY cm.id ASC
")->fetchAll(PDO::FETCH_ASSOC);

$totalIn = array_sum(array_map(fn($m) => $m['type'] === 'in' ? $m['amount'] : 0, $movements));
$totalOut = array_sum(array_map(fn($m) => $m['type'] === 'out' ? $m['amount'] : 0, $movements));
$expectedCalc = $cashRegister['opening_amount'] + $totalIn - $totalOut;

$content = '
<div class="row mb-3">
    <div class="col-md-6">
        <a href="?page=cash" class="btn btn-secondary">← Volver a Caja</a>
    </div>
</div>

<div class="card mb-3">
    <div class="card-header bg-dark text-white">
        <h5 class="mb-0">Detalle de Caja #' . $cashRegister['id'] . '</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-3">
                <p><strong>Fecha Apertura:</strong><br>' . Format::datetime($cashRegister['opened_at']) . '</p>
            </div>
            <div class="col-md-3">
                <p><strong>Fecha Cierre:</strong><br>' . Format::datetime($cashRegister['closed_at']) . '</p>
            </div>
            <div class="col-md-3">
                <p><strong>Usuario:</strong><br>' . htmlspecialchars($cashRegister['user_name']) . '</p>
            </div>
            <div class="col-md-3">
                <p><strong>Estado:</strong><br><span class="badge bg-secondary">' . ucfirst($cashRegister['status']) . '</span></p>
            </div>
        </div>
        <hr>
        <div class="row text-center">
            <div class="col">
                <p class="mb-1">APERTURA</p>
                <h4>' . Format::money($cashRegister['opening_amount']) . '</h4>
            </div>
            <div class="col">
                <p class="mb-1 text-success">+ ENTRADAS</p>
                <h4 class="text-success">' . Format::money($totalIn) . '</h4>
            </div>
            <div class="col">
                <p class="mb-1 text-danger">- SALIDAS</p>
                <h4 class="text-danger">' . Format::money($totalOut) . '</h4>
            </div>
            <div class="col">
                <p class="mb-1 text-primary">= ESPERADO</p>
                <h4 class="text-primary">' . Format::money($expectedCalc) . '</h4>
            </div>
            <div class="col">
                <p class="mb-1">CIERRE</p>
                <h4>' . Format::money($cashRegister['closing_amount'] ?? 0) . '</h4>
            </div>
            <div class="col">';
                $diffCalc = ($cashRegister['closing_amount'] ?? 0) - $cashRegister['opening_amount'];
                $diffClass = $diffCalc > 0 ? 'text-success' : ($diffCalc < 0 ? 'text-danger' : 'text-muted');
                $content .= '<p class="mb-1">DIFERENCIA</p>
                <h4 class="' . $diffClass . '">' . Format::money($diffCalc) . '</h4>';
            $content .= '</div>
        </div>
    </div>
</div>';
