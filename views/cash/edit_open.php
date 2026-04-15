<?php 
$title = 'Editar Apertura';
$pageTitle = 'Editar Apertura de Caja';

$content = '
<div class="row mb-3">
    <div class="col-md-6">
        <a href="?page=cash" class="btn btn-secondary">← Volver</a>
    </div>
</div>

<div class="card mb-3">
    <div class="card-header bg-warning">
        <h5 class="mb-0">Editar Apertura de Caja</h5>
    </div>
    <div class="card-body">
        <form method="POST" action="?page=cash&action=edit_open&id=' . $cr['id'] . '">
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Monto Inicial (Gs.)</label>
                        <input type="number" name="opening_amount" class="form-control" value="' . $cr['opening_amount'] . '" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Observaciones</label>
                        <textarea name="opening_notes" class="form-control" rows="2">' . htmlspecialchars($cr['opening_notes'] ?? '') . '</textarea>
                    </div>
                </div>
            </div>
            <button type="submit" class="btn btn-primary">Guardar Cambios</button>
            <a href="?page=cash&action=cancel&id=' . $cr['id'] . '" class="btn btn-danger" onclick="return confirm(\'Eliminar esta caja?\')">Eliminar Caja</a>
        </form>
    </div>
</div>';
