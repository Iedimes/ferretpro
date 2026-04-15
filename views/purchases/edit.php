<?php 
$title = $action === 'edit' ? 'Editar Compra' : 'Nueva Compra';
$pageTitle = $action === 'edit' ? 'Editar Compra' : 'Nueva Compra';

$content = '
<div class="row mb-3">
    <div class="col-md-6">
        <a href="?page=purchases" class="btn btn-secondary">← Volver</a>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0">' . ($action === 'edit' ? 'Editar Compra' : 'Nueva Compra') . '</h5>
    </div>
    <div class="card-body">
        <form id="purchaseForm" method="post" action="?page=purchases_save">
            <input type="hidden" name="action" value="' . ($action === 'edit' ? 'edit' : 'new') . '">
            ' . ($action === 'edit' && isset($_GET['id']) ? '<input type="hidden" name="purchase_id" value="' . intval($_GET['id']) . '">' : '') . '
            
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">Proveedor</label>
                    <select name="provider_id" class="form-select" required>
                        <option value="">-- Seleccionar proveedor --</option>
                        ';
foreach ($providers as $p) {
    $content .= '<option value="' . $p['id'] . '"' . ($edit_purchase && $edit_purchase['provider_id'] == $p['id'] ? ' selected' : '') . '>' . htmlspecialchars($p['name']) . '</option>';
}
$content .= '
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">N° Factura</label>
                    <input type="text" name="invoice_number" class="form-control" value="' . ($edit_purchase ? htmlspecialchars($edit_purchase['invoice_number']) : '') . '">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Forma de pago</label>
                    <select name="payment_method" class="form-select">
                        <option value="contado"' . ($edit_purchase && $edit_purchase['payment_method'] === 'contado' ? ' selected' : '') . '>Contado</option>
                        <option value="credito"' . ($edit_purchase && $edit_purchase['payment_method'] === 'credito' ? ' selected' : '') . '>Crédito</option>
                    </select>
                </div>
            </div>
            
            <hr>
            
            <h6>Productos</h6>
            <div class="row mb-3">
                <div class="col-md-8">
                    <select id="productSelect" class="form-select">
                        <option value="">-- Seleccionar producto --</option>
                        ';
foreach ($products as $p) {
    $content .= '<option value="' . $p['id'] . '" data-cost="' . $p['cost_price'] . '" data-name="' . htmlspecialchars($p['name']) . '" data-code="' . htmlspecialchars($p['code']) . '">' . htmlspecialchars($p['name']) . ' (Stock: ' . $p['stock'] . ')</option>';
}
$content .= '
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="number" id="productQty" class="form-control" value="1" min="1" placeholder="Cantidad">
                </div>
                <div class="col-md-2">
                    <button type="button" id="addProductBtn" class="btn btn-primary w-100">Agregar</button>
                </div>
            </div>
            
            <table class="table table-bordered" id="itemsTable">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Producto</th>
                        <th>Cantidad</th>
                        <th>Costo Unit.</th>
                        <th>Subtotal</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    ';
if ($purchase_details) {
    foreach ($purchase_details as $item) {
        $content .= '<tr data-id="' . $item['product_id'] . '">
            <td>' . htmlspecialchars($item['code']) . '</td>
            <td>' . htmlspecialchars($item['name']) . '</td>
            <td><input type="number" name="items[' . $item['product_id'] . '][qty]" value="' . $item['quantity'] . '" min="1" class="form-control form-control-sm" style="width:80px"></td>
            <td><input type="number" name="items[' . $item['product_id'] . '][cost]" value="' . $item['unit_cost'] . '" step="0.01" class="form-control form-control-sm" style="width:100px"></td>
            <td class="subtotal">' . Format::money($item['subtotal']) . '</td>
            <td><button type="button" class="btn btn-sm btn-danger remove-item">X</button></td>
        </tr>';
    }
}
$content .= '
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="4" class="text-end"><strong>Total:</strong></td>
                        <td id="totalDisplay"><strong>Gs ' . number_format($edit_purchase['subtotal'] ?? 0, 0, ',', '.') . '</strong></td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
            
            <div class="text-end">
                <button type="submit" class="btn btn-success">Guardar Compra</button>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById("addProductBtn").addEventListener("click", function() {
    var select = document.getElementById("productSelect");
    var qty = parseInt(document.getElementById("productQty").value) || 1;
    var option = select.options[select.selectedIndex];
    
    if (!select.value) return;
    
    var productId = select.value;
    var productName = option.dataset.name;
    var productCode = option.dataset.code;
    var cost = parseFloat(option.dataset.cost) || 0;
    
    var tbody = document.querySelector("#itemsTable tbody");
    var existing = tbody.querySelector("tr[data-id=\"" + productId + "\"]");
    
    if (existing) {
        var qtyInput = existing.querySelector("input[name*=qty]");
        qtyInput.value = parseInt(qtyInput.value) + qty;
        updateRow(existing);
    } else {
        var row = document.createElement("tr");
        row.dataset.id = productId;
        row.innerHTML = 
            "<td>" + productCode + "</td>" +
            "<td>" + productName + "</td>" +
            "<td><input type=\"number\" name=\"items[" + productId + "][qty]\" value=\"" + qty + "\" min=\"1\" class=\"form-control form-control-sm\" style=\"width:80px\"></td>" +
            "<td><input type=\"number\" name=\"items[" + productId + "][cost]\" value=\"" + cost.toFixed(0) + "\" step=\"1\" class=\"form-control form-control-sm\" style=\"width:100px\"></td>" +
            "<td class=\"subtotal\">" + (cost * qty).toLocaleString("es-PY") + "</td>" +
            "<td><button type=\"button\" class=\"btn btn-sm btn-danger remove-item\">X</button></td>";
        
        row.querySelectorAll("input").forEach(function(input) {
            input.addEventListener("change", function() { updateRow(row); });
        });
        row.querySelector(".remove-item").addEventListener("click", function() { row.remove(); calculateTotals(); });
        tbody.appendChild(row);
    }
    
    select.value = "";
    document.getElementById("productQty").value = 1;
    calculateTotals();
});

function updateRow(row) {
    var qty = parseInt(row.querySelector("input[name*=qty]").value) || 1;
    var cost = parseFloat(row.querySelector("input[name*=cost]").value) || 0;
    row.querySelector(".subtotal").textContent = (cost * qty).toLocaleString("es-PY");
    calculateTotals();
}

function calculateTotals() {
    var total = 0;
    document.querySelectorAll("#itemsTable tbody tr").forEach(function(row) {
        var qty = parseInt(row.querySelector("input[name*=qty]").value) || 1;
        var cost = parseFloat(row.querySelector("input[name*=cost]").value) || 0;
        total += cost * qty;
    });
    
    document.getElementById("totalDisplay").innerHTML = "<strong>Gs " + total.toLocaleString("es-PY") + "</strong>";
}

document.querySelectorAll(".remove-item").forEach(function(btn) {
    btn.addEventListener("click", function() {
        this.closest("tr").remove();
        calculateTotals();
    });
});

calculateTotals();
</script>';