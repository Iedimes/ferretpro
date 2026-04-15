<?php 
$title = 'Nuevo Gasto';
$pageTitle = 'Registrar Gasto';

$content = '
<div class="row mb-3">
    <div class="col-md-6">
        <a href="?page=expenses" class="btn btn-secondary">← Volver</a>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Registrar Gasto</h5>
    </div>
    <div class="card-body">
        <form method="post" action="?page=expenses_save">
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">Categoría</label>
                    <select name="category" class="form-select" required>
                        <option value="">-- Seleccionar --</option>
                        <option value="Útiles">Útiles de oficina</option>
                        <option value="Servicios">Servicios (luz, agua, internet)</option>
                        <option value="Mantenimiento">Mantenimiento</option>
                        <option value="Alquiler">Alquiler</option>
                        <option value="Impuestos">Impuestos</option>
                        <option value="Transporte">Transporte</option>
                        <option value="Otro">Otro</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Monto (Gs.)</label>
                    <input type="number" name="amount" class="form-control" required min="1">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Fecha</label>
                    <input type="date" name="date" class="form-control" value="' . date('Y-m-d') . '">
                </div>
            </div>
            
            <div class="row mb-3">
                <div class="col-md-4">
                    <label class="form-label">Método de Pago</label>
                    <select name="payment_method" class="form-select" required>
                        <option value="efectivo">Efectivo</option>
                        <option value="transferencia">Transferencia</option>
                        <option value="tarjeta">Tarjeta</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Cuenta</label>
                    <select name="cuenta" class="form-select" required>
                        <option value="caja">Caja Física</option>
                        <option value="banco">Banco</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Referencia</label>
                    <input type="text" name="referencia" class="form-control" placeholder="N° transferencia">
                </div>
            </div>
            
            <div class="row mb-3">
                <div class="col-12">
                    <label class="form-label">Descripción</label>
                    <textarea name="description" class="form-control" rows="2" placeholder="Detalle del gasto"></textarea>
                </div>
            </div>
            
            <div class="text-end">
                <button type="submit" class="btn btn-success">Registrar Gasto</button>
            </div>
        </form>
    </div>
</div>';