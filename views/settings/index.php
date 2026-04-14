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
                    <label class="form-label">Establecimiento (Sucursal)</label>
                    <input type="text" name="invoice_establishment" class="form-control" placeholder="001" value="' . htmlspecialchars($settings['invoice_establishment'] ?? '001') . '">
                    <small class="text-muted">001 = Casa Matriz, 002 = Sucursal 2, etc.</small>
                </div>
                <div class="mb-3">
                    <label class="form-label">Punto de Expedición (Caja)</label>
                    <input type="text" name="invoice_pos" class="form-control" placeholder="001" value="' . htmlspecialchars($settings['invoice_pos'] ?? '001') . '">
                    <small class="text-muted">001 = Caja 1, 002 = Caja 2, etc.</small>
                </div>
                <div class="mb-3">
                    <label class="form-label">Actividad Económica</label>
                    <input type="text" name="company_activity" class="form-control" placeholder="COMERCIO AL POR MENOR..." value="' . htmlspecialchars($settings['company_activity'] ?? '') . '">
                </div>
                <div class="mb-3">
                    <label class="form-label">Alerta de Stock Bajo</label>
                    <input type="number" name="low_stock_alert" class="form-control" value="' . htmlspecialchars($settings['low_stock_alert'] ?? '5') . '">
                </div>
                <hr>
                <div class="mb-3">
                    <label class="form-label">Tipo de Factura</label>
                    <select name="invoice_type" class="form-select">
                        <option value="letter"' . ($settings['invoice_type'] ?? '' === 'letter' ? 'selected' : '') . '>Carta (A4)</option>
                        <option value="thermal"' . ($settings['invoice_type'] ?? '' === 'thermal' ? 'selected' : '') . '>Termal (58mm)</option>
                    </select>
                </div>
                <div class="mt-3">
                    <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card mb-3">
            <div class="card-header">
                <h5 class="mb-0">Timbrado Fiscal</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">Número de Timbrado</label>
                    <input type="text" name="timbrado_num" class="form-control" placeholder="18672488" value="' . htmlspecialchars($settings['timbrado_num'] ?? '') . '">
                </div>
                <div class="mb-3">
                    <label class="form-label">Fecha Inicio Vigencia</label>
                    <input type="date" name="timbrado_inicio" class="form-control" value="' . htmlspecialchars($settings['timbrado_inicio'] ?? date('Y-m-d')) . '">
                </div>
                <div class="mb-3">
                    <label class="form-label">Fecha Fin Vigencia</label>
                    <input type="date" name="timbrado_fin" class="form-control" value="' . htmlspecialchars($settings['timbrado_fin'] ?? date('Y-m-d', strtotime('+1 year'))) . '">
                </div>
                <div class="mb-3">
                    <label class="form-label">Dirección Empresa</label>
                    <input type="text" name="company_address" class="form-control" placeholder="Asunción, Paraguay" value="' . htmlspecialchars($settings['company_address'] ?? '') . '">
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
