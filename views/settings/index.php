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
                    <label class="form-label">CUIT / Documento</label>
                    <input type="text" name="company_document" class="form-control" value="' . htmlspecialchars($settings['company_document'] ?? '') . '">
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card mb-3">
            <div class="card-header">
                <h5 class="mb-0">Parámetros</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">Prefijo de Factura</label>
                    <input type="text" name="invoice_prefix" class="form-control" value="' . htmlspecialchars($settings['invoice_prefix'] ?? 'A') . '">
                </div>
                <div class="mb-3">
                    <label class="form-label">% IVA</label>
                    <input type="number" name="iva_percentage" class="form-control" value="' . htmlspecialchars($settings['iva_percentage'] ?? '21') . '">
                </div>
                <div class="mb-3">
                    <label class="form-label">Alerta de Stock Bajo</label>
                    <input type="number" name="low_stock_alert" class="form-control" value="' . htmlspecialchars($settings['low_stock_alert'] ?? '5') . '">
                </div>
            </div>
        </div>
    </div>
</form>

<div class="row mt-4">
    <div class="col-md-6">
        <button type="submit" class="btn btn-primary">Guardar Cambios</button>
    </div>
</div>

<div class="card mt-4">
    <div class="card-header">
        <h5 class="mb-0">Información del Sistema</h5>
    </div>
    <div class="card-body">
        <p><strong>Versión:</strong> 1.0.0</p>
        <p><strong>Usuario:</strong> ' . (user()['name'] ?? '') . '</p>
    </div>
</div>';
