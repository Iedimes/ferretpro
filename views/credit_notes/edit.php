<?php 
$title = 'Nueva Nota de Crédito';
$pageTitle = 'Nueva Nota de Crédito';

$content = '
<div class="row mb-3">
    <div class="col-md-6">
        <a href="?page=credit_notes" class="btn btn-secondary">← Volver</a>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Nueva Nota de Crédito</h5>
    </div>
    <div class="card-body">
        <form id="cnForm" method="post" action="?page=credit_notes_save">
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">Venta relacionada</label>
                    <select name="sale_id" class="form-select" id="saleSelect" required>
                        <option value="">-- Seleccionar venta --</option>
                        ';
foreach ($sales as $s) {
    $content .= '<option value="' . $s['id'] . '" data-client="' . htmlspecialchars($s['client_name'] ?? 'Mostrador') . '" data-total="' . $s['total'] . '">Venta #' . $s['id'] . ' - ' . htmlspecialchars($s['client_name'] ?? 'Mostrador') . ' - ' . Format::money($s['total']) . '</option>';
}
$content .= '
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Cliente</label>
                    <input type="text" class="form-control" id="clientName" readonly>
                </div>
            </div>
            
            <div class="row mb-3">
                <div class="col-12">
                    <label class="form-label">Motivo de la devolución</label>
                    <select name="reason" class="form-select" required>
                        <option value="">-- Seleccionar motivo --</option>
                        <option value="producto_defectuoso">Producto defectuoso</option>
                        <option value="producto_incorrecto">Producto incorrecto</option>
                        <option value="no_necesita">Ya no lo necesita</option>
                        <option value="otro">Otro</option>
                    </select>
                </div>
            </div>
            
            <hr>
            
            <h6>Productos a devolver</h6>
            <div class="row mb-3">
                <div class="col-md-8">
                    <select id="productSelect" class="form-select">
                        <option value="">-- Seleccionar producto --</option>
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
                <tbody></tbody>
                <tfoot>
                    <tr>
                        <td colspan="4" class="text-end"><strong>Total:</strong></td>
                        <td id="totalDisplay"><strong>Gs 0</strong></td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
            
            <div class="text-end">
                <button type="submit" class="btn btn-success">Crear Nota de Crédito</button>
            </div>
        </form>
    </div>
</div>

<script>
var saleProducts = {};

document.getElementById("saleSelect").addEventListener("change", function() {
    var option = this.options[this.selectedIndex];
    document.getElementById("clientName").value = option.dataset.client || "";
    
    // Cargar productos de la venta
    var saleId = this.value;
    if (!saleId) return;
    
    fetch("?page=sale_products&sale_id=" + saleId)
        .then(function(response) { return response.json(); })
        .then(function(data) {
            saleProducts = data;
            var select = document.getElementById("productSelect");
            select.innerHTML = "<option value=\"\">-- Seleccionar producto --</option>";
            data.forEach(function(p) {
                select.innerHTML += "<option value=\"" + p.product_id + "\" data-price=\"" + p.unit_price + "\" data-name=\"" + p.name + "\" data-code=\"" + p.code + "\">" + p.name + " - " + p.quantity + " disponibles</option>";
            });
        });
});

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
    var total = 0;
    document.querySelectorAll("#itemsTable tbody tr").forEach(function(row) {
        var qty = parseInt(row.querySelector("input[name*=qty]").value) || 1;
        var price = parseFloat(row.querySelector("input[name*=price]").value) || 0;
        total += price * qty;
    });
    
    document.getElementById("totalDisplay").innerHTML = "<strong>Gs " + total.toLocaleString("es-PY") + "</strong>";
}

document.querySelectorAll(".remove-item").forEach(function(btn) {
    btn.addEventListener("click", function() {
        this.closest("tr").remove();
        calculateTotals();
    });
});
</script>';