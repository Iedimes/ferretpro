<?php 
$title = 'Configuración';
$pageTitle = 'Configuración del Sistema';

$content = '
<form method="POST" class="row">
    <div class="col-md-6">
        <div class="card mb-3">
            <div class="card-header">
                <h5 class="mb-0">Datos de la Empresa</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">Nombre de la Empresa</label>
                    <input type="text" name="company_name" class="form-control" value="' . htmlspecialchars($settings['company_name'] ?? '') . '">
                </div>
                <div class="mb-3">
                    <label class="form-label">Teléfono</label>
                    <input type="text" name="company_phone" class="form-control" value="' . htmlspecialchars($settings['company_phone'] ?? '') . '">
                </div>
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="company_email" class="form-control" value="' . htmlspecialchars($settings['company_email'] ?? '') . '">
                </div>
                <div class="mb-3">
                    <label class="form-label">RUC / Documento</label>
                    <input type="text" name="company_document" class="form-control" placeholder="Ej: 80012345-9" value="' . htmlspecialchars($settings['company_document'] ?? '') . '">
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card mb-3">
            <div class="card-header">
                <h5 class="mb-0">Facturación</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">Punto de Venta</label>
                    <input type="text" name="invoice_pos" class="form-control" placeholder="001" value="' . htmlspecialchars($settings['invoice_pos'] ?? '001') . '">
                    <small class="text-muted">Formato: 001 para punto de venta</small>
                </div>
                <div class="mb-3">
                    <label class="form-label">Prefijo Factura</label>
                    <input type="text" name="invoice_prefix" class="form-control" placeholder="001-001-" value="' . htmlspecialchars($settings['invoice_prefix'] ?? '001-001-') . '">
                    <small class="text-muted">Formato Paraguay: 001-001- (punto-número)</small>
                </div>
                <div class="mb-3">
                    <label class="form-label">Alerta de Stock Bajo</label>
                    <input type="number" name="low_stock_alert" class="form-control" value="' . htmlspecialchars($settings['low_stock_alert'] ?? '5') . '">
                </div>
                <div class="mt-3">
                    <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                </div>
            </div>
        </div>
    </div>
</form>

<div class="card mt-4">
    <div class="card-header">
        <h5 class="mb-0">Información del Sistema</h5>
    </div>
    <div class="card-body">
        <p><strong>Versión:</strong> 1.0.0</p>
        <p><strong>Usuario:</strong> ' . (user()['name'] ?? '') . '</p>
    </div>
</div>';
