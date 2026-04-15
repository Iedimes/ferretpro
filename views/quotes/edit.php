<?php 
$title = $action === 'edit' ? 'Editar Cotización' : 'Nueva Cotización';
$pageTitle = $action === 'edit' ? 'Editar Cotización' : 'Nueva Cotización';

$content = '
<div class="row mb-3">
    <div class="col-md-6">
        <a href="?page=quotes" class="btn btn-secondary">← Volver</a>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0">' . ($action === 'edit' ? 'Editar Cotización' : 'Nueva Cotización') . '</h5>
    </div>
    <div class="card-body">
        <form id="quoteForm" method="post" action="?page=quotes_save">
            <input type="hidden" name="action" value="' . ($action === 'edit' ? 'edit' : 'new') . '">
            ' . ($action === 'edit' && isset($_GET['id']) ? '<input type="hidden" name="quote_id" value="' . intval($_GET['id']) . '">' : '') . '
            
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">Cliente</label>
                    <select name="client_id" class="form-select" id="clientSelect">
                        <option value="">-- Sin cliente --</option>
                        ';
foreach ($clients as $c) {
    $content .= '<option value="' . $c['id'] . '"' . ($edit_quote && $edit_quote['client_id'] == $c['id'] ? ' selected' : '') . '>' . htmlspecialchars($c['name']) . ' (' . htmlspecialchars($c['document']) . ')</option>';
}
$content .= '
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Validez (días)</label>
                    <input type="number" name="validity_days" class="form-control" value="' . ($edit_quote ? $edit_quote['validity_days'] : 30) . '" min="1">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Descuento %</label>
                    <input type="number" name="discount" class="form-control" value="' . ($edit_quote ? $edit_quote['discount'] : 0) . '" min="0" max="100" step="0.01">
                </div>
            </div>
            
            <div class="row mb-3">
                <div class="col-12">
                    <label class="form-label">Notas</label>
                    <textarea name="notes" class="form-control" rows="2">' . ($edit_quote ? htmlspecialchars($edit_quote['notes']) : '') . '</textarea>
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
    $content .= '<option value="' . $p['id'] . '" data-price="' . $p['sale_price'] . '" data-name="' . htmlspecialchars($p['name']) . '" data-code="' . htmlspecialchars($p['code']) . '">' . htmlspecialchars($p['name']) . ' - ' . Format::money($p['sale_price']) . '</option>';
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
                        <th>Precio</th>
                        <th>Subtotal</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    ';
if ($quote_details) {
    foreach ($quote_details as $item) {
        $content .= '<tr data-id="' . $item['product_id'] . '">
            <td>' . htmlspecialchars($item['code']) . '</td>
            <td>' . htmlspecialchars($item['name']) . '</td>
            <td><input type="number" name="items[' . $item['product_id'] . '][qty]" value="' . $item['quantity'] . '" min="1" class="form-control form-control-sm" style="width:80px"></td>
            <td><input type="number" name="items[' . $item['product_id'] . '][price]" value="' . $item['unit_price'] . '" step="0.01" class="form-control form-control-sm" style="width:100px"></td>
            <td class="subtotal">' . Format::money($item['subtotal']) . '</td>
            <td><button type="button" class="btn btn-sm btn-danger remove-item">X</button></td>
        </tr>';
    }
}
$content .= '
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="4" class="text-end"><strong>Subtotal:</strong></td>
                        <td id="subtotalDisplay">Gs ' . number_format($edit_quote['subtotal'] ?? 0, 0, ',', '.') . '</td>
                        <td></td>
                    </tr>
                    <tr>
                        <td colspan="4" class="text-end"><strong>Descuento:</strong></td>
                        <td id="discountDisplay">Gs ' . number_format($edit_quote['discount'] ?? 0, 0, ',', '.') . '</td>
                        <td></td>
                    </tr>
                    <tr>
                        <td colspan="4" class="text-end"><strong>Total:</strong></td>
                        <td id="totalDisplay"><strong>Gs ' . number_format($edit_quote['total'] ?? 0, 0, ',', '.') . '</strong></td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
            
            <div class="text-end">
                <button type="submit" class="btn btn-success">Guardar Cotización</button>
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
    var price = parseFloat(option.dataset.price);
    
    var tbody = document.querySelector("#itemsTable tbody");
    var existing = tbody.querySelector("tr[data-id=\"" + productId + "\"]");
    
    if (existing) {
        var qtyInput = existing.querySelector("input[type=number]");
        qtyInput.value = parseInt(qtyInput.value) + qty;
        updateRow(existing);
    } else {
        var row = document.createElement("tr");
        row.dataset.id = productId;
        row.innerHTML = 
            "<td>" + productCode + "</td>" +
            "<td>" + productName + "</td>" +
            "<td><input type=\"number\" name=\"items[" + productId + "][qty]\" value=\"" + qty + "\" min=\"1\" class=\"form-control form-control-sm\" style=\"width:80px\"></td>" +
            "<td><input type=\"number\" name=\"items[" + productId + "][price]\" value=\"" + price.toFixed(0) + "\" step=\"1\" class=\"form-control form-control-sm\" style=\"width:100px\"></td>" +
            "<td class=\"subtotal\">" + (price * qty).toLocaleString("es-PY") + "</td>" +
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
    var price = parseFloat(row.querySelector("input[name*=price]").value) || 0;
    row.querySelector(".subtotal").textContent = (price * qty).toLocaleString("es-PY");
    calculateTotals();
}

function calculateTotals() {
    var subtotal = 0;
    document.querySelectorAll("#itemsTable tbody tr").forEach(function(row) {
        var qty = parseInt(row.querySelector("input[name*=qty]").value) || 1;
        var price = parseFloat(row.querySelector("input[name*=price]").value) || 0;
        subtotal += price * qty;
    });
    
    var discountPercent = parseFloat(document.querySelector("input[name=discount]").value) || 0;
    var discount = subtotal * (discountPercent / 100);
    var total = subtotal - discount;
    
    document.getElementById("subtotalDisplay").textContent = "Gs " + subtotal.toLocaleString("es-PY");
    document.getElementById("discountDisplay").textContent = "Gs " + discount.toLocaleString("es-PY");
    document.getElementById("totalDisplay").innerHTML = "<strong>Gs " + total.toLocaleString("es-PY") + "</strong>";
}

document.querySelector("input[name=discount]").addEventListener("change", calculateTotals);

document.querySelectorAll(".remove-item").forEach(function(btn) {
    btn.addEventListener("click", function() {
        this.closest("tr").remove();
        calculateTotals();
    });
});
</script>';