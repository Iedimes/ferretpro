<?php
$title = 'Ventas';
$pageTitle = 'Editar Venta';

// Usar variable pasada por view() o GET
$sale_id = intval($edit_sale_id ?? $_GET['id'] ?? 0);

if ($sale_id == 0) {
    $_SESSION['flash']['error'] = 'ID de venta no válido';
    header('Location: ?page=sales');
    exit;
}

// Obtener datos de la venta
$stmt = db()->prepare("SELECT s.*, u.name as user_name, c.name as client_name, c.phone as client_phone, c.address as client_address 
    FROM sales s 
    LEFT JOIN users u ON s.user_id = u.id 
    LEFT JOIN clients c ON s.client_id = c.id 
    WHERE s.id = ?");
$stmt->execute([$sale_id]);
$sale = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$sale) {
    $_SESSION['flash']['error'] = 'Venta no encontrada';
    header('Location: ?page=sales');
    exit;
}

// Obtener detalles de la venta
$stmtDetails = db()->prepare("SELECT sd.*, p.name as product_name, p.code as product_code, p.stock as current_stock, p.sale_price 
    FROM sale_details sd 
    LEFT JOIN products p ON sd.product_id = p.id 
    WHERE sd.sale_id = ?");
$stmtDetails->execute([$sale_id]);
$saleDetails = $stmtDetails->fetchAll(PDO::FETCH_ASSOC);

// Obtener todos los productos para el autocomplete
$products = db()->query("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.active = 1 ORDER BY p.name")->fetchAll(PDO::FETCH_ASSOC);

// Obtener clientes para el dropdown
$clients = db()->query("SELECT id, name, balance FROM clients WHERE active = 1 ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);

// Procesar actualización
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_sale') {
    try {
        // Obtener valores del formulario
        $newClientId = $_POST['client_id'] ?: null;
        $newSaleType = $_POST['sale_type'] ?? 'contado';
        $newPaymentMethod = $_POST['payment_method'] ?? 'efectivo';
        $newDeliveryType = $_POST['delivery_type'] ?? 'mostrador';
        $discountPercent = floatval($_POST['discount_percent'] ?? 0);
        $notes = trim($_POST['notes'] ?? '');
        
        // Obtener detalles originales para restaurar stock
        $originalDetails = db()->prepare("SELECT product_id, quantity FROM sale_details WHERE sale_id = ?");
        $originalDetails->execute([$sale_id]);
        $originalItems = $originalDetails->fetchAll(PDO::FETCH_ASSOC);
        
        // Restaurar stock de productos originales
        foreach ($originalItems as $item) {
            $stmtRestore = db()->prepare("UPDATE products SET stock = stock + ? WHERE id = ?");
            $stmtRestore->execute([$item['quantity'], $item['product_id']]);
        }
        
        // Eliminar detalles antiguos
        $stmtDeleteDetails = db()->prepare("DELETE FROM sale_details WHERE sale_id = ?");
        $stmtDeleteDetails->execute([$sale_id]);
        
        // Manejar cuentas por cobrar (eliminar las anteriores)
        if ($sale['type'] === 'credito' && $sale['client_id']) {
            $stmtReceivable = db()->prepare("DELETE FROM accounts_receivable WHERE sale_id = ?");
            $stmtReceivable->execute([$sale_id]);
            
            $stmtBalance = db()->prepare("UPDATE clients SET balance = balance - ? WHERE id = ?");
            $stmtBalance->execute([$sale['total'], $sale['client_id']]);
        }
        
        // Procesar nuevos items
        $items = json_decode($_POST['items'] ?? '[]', true);
        $newSubtotal = 0;
        
        foreach ($items as $item) {
            $productId = intval($item['id']);
            $qty = intval($item['qty']);
            $unitPrice = floatval($item['price']);
            $itemSubtotal = $unitPrice * $qty;
            
            // Insertar nuevo detalle
            $stmtInsertDetail = db()->prepare("INSERT INTO sale_details (sale_id, product_id, quantity, unit_price, subtotal) VALUES (?, ?, ?, ?, ?)");
            $stmtInsertDetail->execute([$sale_id, $productId, $qty, $unitPrice, $itemSubtotal]);
            
            // Actualizar stock (restar)
            $stmtUpdateStock = db()->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
            $stmtUpdateStock->execute([$qty, $productId]);
            
            $newSubtotal += $itemSubtotal;
        }
        
        // Calcular nuevo total
        $discountAmount = $newSubtotal * ($discountPercent / 100);
        $newTotal = $newSubtotal - $discountAmount;
        
        // Actualizar venta con TODOS los campos
        $stmtUpdateSale = db()->prepare("UPDATE sales SET client_id = ?, type = ?, payment_method = ?, delivery_type = ?, subtotal = ?, discount = ?, total = ?, notes = ? WHERE id = ?");
        $stmtUpdateSale->execute([$newClientId, $newSaleType, $newPaymentMethod, $newDeliveryType, $newSubtotal, $discountAmount, $newTotal, $notes, $sale_id]);
        
        // Si es venta a credito, crear nueva cuenta por cobrar
        if ($newSaleType === 'credito' && $newClientId) {
            $stmtClientCredit = db()->prepare("SELECT credit_days FROM clients WHERE id = ?");
            $stmtClientCredit->execute([$newClientId]);
            $creditDays = $stmtClientCredit->fetchColumn() ?: 30;
            
            $dueDate = date('Y-m-d', strtotime("+{$creditDays} days"));
            
            $stmtReceivable = db()->prepare("INSERT INTO accounts_receivable (sale_id, client_id, amount, due_date, status, created_at) VALUES (?, ?, ?, ?, 'pendiente', datetime('now'))");
            $stmtReceivable->execute([$sale_id, $newClientId, $newTotal, $dueDate]);
            
            $stmtBalance = db()->prepare("UPDATE clients SET balance = balance + ? WHERE id = ?");
            $stmtBalance->execute([$newTotal, $newClientId]);
        }
        
        $_SESSION['flash']['success'] = 'Venta actualizada correctamente';
        header('Location: ?page=sales');
        exit;
        
    } catch (Exception $e) {
        $_SESSION['flash']['error'] = 'Error al actualizar: ' . $e->getMessage();
    }
}

// Calcular totales
$currentSubtotal = $sale['subtotal'] ?? 0;
$currentDiscount = $sale['discount'] ?? 0;
$currentTotal = $sale['total'] ?? 0;
$discountPercent = $currentSubtotal > 0 ? round(($currentDiscount / $currentSubtotal) * 100) : 0;

// Preparar items para JavaScript
$itemsJson = json_encode(array_map(function($d) {
    return [
        'id' => $d['product_id'],
        'name' => $d['product_name'],
        'code' => $d['product_code'],
        'price' => $d['unit_price'],
        'qty' => $d['quantity']
    ];
}, $saleDetails));

$content = '
<div class="row mb-3">
    <div class="col-md-6">
        <h4>Editar Venta #' . $sale['id'] . '</h4>
    </div>
    <div class="col-md-6 text-end">
        <a href="?page=sales" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Volver</a>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card mb-3">
            <div class="card-header">
                <strong>Productos de la Venta</strong>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Producto</th>
                            <th width="100">Precio</th>
                            <th width="100">Cantidad</th>
                            <th width="120">Subtotal</th>
                            <th width="50"></th>
                        </tr>
                    </thead>
                    <tbody id="itemsTableBody">
';

foreach ($saleDetails as $detail) {
    $productName = $detail['product_name'] ?? 'Producto eliminado #' . $detail['product_id'];
    $productCode = $detail['product_code'] ?? 'N/A';
    $content .= '<tr id="item-' . $detail['product_id'] . '">
        <td>' . htmlspecialchars($productName) . ' <small class="text-muted">(' . $productCode . ')</small></td>
        <td>Gs. ' . number_format($detail['unit_price'], 0, '', '.') . '</td>
        <td><input type="number" class="form-control form-control-sm" value="' . $detail['quantity'] . '" min="1" onchange="updateQty(' . $detail['product_id'] . ', this.value)"></td>
        <td>Gs. ' . number_format($detail['subtotal'], 0, '', '.') . '</td>
        <td><button class="btn btn-sm btn-danger" onclick="removeItem(' . $detail['product_id'] . ')"><i class="bi bi-trash"></i></button></td>
    </tr>';
}

$content .= '</tbody>
                </table>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <strong>Agregar Producto</strong>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8">
                        <input type="text" id="searchProduct" class="form-control" placeholder="Buscar producto..." autocomplete="off">
                        <div id="searchResults" class="list-group position-absolute" style="z-index: 1000; max-height: 300px; overflow-y: auto; display: none; width: 100%;"></div>
                    </div>
                    <div class="col-md-4">
                        <button type="button" class="btn btn-primary w-100" onclick="addSelectedProduct()">Agregar</button>
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="col-md-6">
                        <input type="number" id="addQty" class="form-control" placeholder="Cantidad" value="1" min="1">
                    </div>
                    <div class="col-md-6">
                        <input type="number" id="addPrice" class="form-control" placeholder="Precio de venta" value="0">
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card mb-3">
            <div class="card-header">
                <strong>Datos de la Venta</strong>
            </div>
            <div class="card-body">
                <p><strong>Fecha:</strong> ' . Format::datetime($sale['created_at']) . '</p>
                <p><strong>Vendedor:</strong> ' . htmlspecialchars($sale['user_name']) . '</p>
                
                <div class="mb-3">
                    <label class="form-label"><strong>Cliente</strong></label>
                    <select name="client_id" class="form-select">
                        <option value="">Mostrador</option>';
foreach ($clients as $c) {
    $selected = ($c['id'] == $sale['client_id']) ? 'selected' : '';
    $balanceText = $c['balance'] > 0 ? ' (Deuda: Gs. ' . number_format($c['balance'], 0, '', '.') . ')' : '';
    $content .= '<option value="' . $c['id'] . '" ' . $selected . '>' . htmlspecialchars($c['name']) . $balanceText . '</option>';
}
$content .= '</select>
                </div>
                
                <div class="mb-3">
                    <label class="form-label"><strong>Tipo de Venta</strong></label>
                    <select name="sale_type" id="saleTypeSelect" class="form-select" onchange="checkCreditClient()">
                        <option value="contado" ' . ($sale['type'] === 'contado' ? 'selected' : '') . '>Contado</option>
                        <option value="credito" ' . ($sale['type'] === 'credito' ? 'selected' : '') . '>Credito</option>
                    </select>
                </div>
                
                <div class="mb-3">
                    <label class="form-label"><strong>Metodo de Pago</strong></label>
                    <select name="payment_method" class="form-select">
                        <option value="efectivo" ' . ($sale['payment_method'] === 'efectivo' ? 'selected' : '') . '>Efectivo</option>
                        <option value="tarjeta" ' . ($sale['payment_method'] === 'tarjeta' ? 'selected' : '') . '>Tarjeta</option>
                        <option value="transferencia" ' . ($sale['payment_method'] === 'transferencia' ? 'selected' : '') . '>Transferencia</option>
                        <option value="mixto" ' . ($sale['payment_method'] === 'mixto' ? 'selected' : '') . '>Mixto</option>
                    </select>
                </div>
                
                <div class="mb-3">
                    <label class="form-label"><strong>Tipo de Entrega</strong></label>
                    <select name="delivery_type" class="form-select">
                        <option value="mostrador" ' . ($sale['delivery_type'] === 'mostrador' ? 'selected' : '') . '>Mostrador</option>
                        <option value="delivery" ' . ($sale['delivery_type'] === 'delivery' ? 'selected' : '') . '>Delivery</option>
                        <option value="pendiente" ' . ($sale['delivery_type'] === 'pendiente' ? 'selected' : '') . '>Pendiente (Retirar luego)</option>
                    </select>
                </div>';
                
                // Si es venta a credito, mostrar fecha de vencimiento
                if ($sale['type'] === 'credito' && $sale['client_id']) {
                    $receivable = db()->prepare("SELECT due_date FROM accounts_receivable WHERE sale_id = ? LIMIT 1");
                    $receivable->execute([$sale['id']]);
                    $dueDate = $receivable->fetchColumn();
                    
                    if ($dueDate) {
                        $dueDateObj = new DateTime($dueDate);
                        $today = new DateTime();
                        $isOverdue = $dueDateObj < $today;
                        $badgeClass = $isOverdue ? 'bg-danger' : 'bg-warning';
                        
                        $content .= '<div class="alert alert-' . ($isOverdue ? 'danger' : 'warning') . ' mt-2">
                            <strong>Fecha Vencimiento:</strong> ' . Format::date($dueDate) . ($isOverdue ? ' (VENCIDA)' : '') . '
                        </div>';
                    }
                }
                
$content .= '</div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <strong>Totales y Descuento</strong>
            </div>
            <div class="card-body">
                <form method="POST" id="updateSaleForm">
                    <input type="hidden" name="action" value="update_sale">
                    <input type="hidden" name="items" id="saleItems">
                    
                    <div class="mb-3">
                        <label class="form-label">Descuento (%)</label>
                        <input type="number" name="discount_percent" id="discountInput" class="form-control" value="' . $discountPercent . '" min="0" max="100">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Notas</label>
                        <textarea name="notes" class="form-control" rows="2">' . htmlspecialchars($sale['notes'] ?? '') . '</textarea>
                    </div>
                    
                    <hr>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Subtotal:</span>
                        <span id="editSubtotal" class="fw-bold">Gs. ' . number_format($currentSubtotal, 0, '', '.') . '</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Descuento:</span>
                        <span id="editDiscount" class="text-danger">Gs. ' . number_format($currentDiscount, 0, '', '.') . '</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2 fs-5">
                        <span><strong>Total:</strong></span>
                        <span id="editTotal" class="text-success fw-bold">Gs. ' . number_format($currentTotal, 0, '', '.') . '</span>
                    </div>
                    
                    <button type="submit" class="btn btn-success w-100 mt-3">
                        <i class="bi bi-check-circle"></i> Guardar Cambios
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// Productos disponibles
const allProducts = ' . json_encode(array_map(function($p) {
    return [
        'id' => $p['id'],
        'name' => $p['name'],
        'code' => $p['code'],
        'barcode' => $p['barcode'] ?? '',
        'sale_price' => $p['sale_price'],
        'stock' => $p['stock']
    ];
}, $products)) . ';

// Items actuales
let editItems = ' . $itemsJson . ';
let selectedProduct = null;

// Función para formatear dinero
function formatMoney(num) {
    return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
}

function updateTotals() {
    let subtotal = 0;
    editItems.forEach(item => {
        subtotal += item.price * item.qty;
    });
    
    const discountPercent = parseFloat(document.getElementById("discountInput").value) || 0;
    const discountAmount = subtotal * (discountPercent / 100);
    const total = subtotal - discountAmount;
    
    document.getElementById("editSubtotal").textContent = "Gs. " + formatMoney(subtotal);
    document.getElementById("editDiscount").textContent = "Gs. " + formatMoney(discountAmount);
    document.getElementById("editTotal").textContent = "Gs. " + formatMoney(total);
    
    document.getElementById("saleItems").value = JSON.stringify(editItems);
}

document.getElementById("discountInput").addEventListener("input", updateTotals);

function removeItem(productId) {
    editItems = editItems.filter(item => item.id !== productId);
    renderItems();
    updateTotals();
}

function updateQty(productId, qty) {
    const item = editItems.find(i => i.id === productId);
    if (item) {
        item.qty = parseInt(qty);
        updateTotals();
        renderItems();
    }
}

function renderItems() {
    const tbody = document.getElementById("itemsTableBody");
    let html = "";
    
    if (editItems.length === 0) {
        html = "<tr><td colspan=\"5\" class=\"text-center text-muted\">No hay productos</td></tr>";
    } else {
        editItems.forEach(item => {
            const product = allProducts.find(p => p.id === item.id);
            const productName = product ? product.name : item.name;
            const productCode = product ? product.code : item.code;
            
            html += `<tr id=\"item-${item.id}\">
                <td>${productName} <small class=\"text-muted\">(${productCode})</small></td>
                <td>Gs. ${formatMoney(item.price)}</td>
                <td><input type=\"number\" class=\"form-control form-control-sm\" value=\"${item.qty}\" min=\"1\" onchange=\"updateQty(${item.id}, this.value)\"></td>
                <td>Gs. ${formatMoney(item.price * item.qty)}</td>
                <td><button class=\"btn btn-sm btn-danger\" onclick=\"removeItem(${item.id})\"><i class=\"bi bi-trash\"></i></button></td>
            </tr>`;
        });
    }
    
    tbody.innerHTML = html;
}

document.getElementById("searchProduct").addEventListener("input", function() {
    const term = this.value.toLowerCase().trim();
    const results = document.getElementById("searchResults");
    
    if (term.length < 2) {
        results.style.display = "none";
        return;
    }
    
    const filtered = allProducts.filter(p => 
        p.name.toLowerCase().includes(term) || 
        (p.code && p.code.toLowerCase().includes(term)) ||
        (p.barcode && p.barcode.toLowerCase().includes(term))
    ).slice(0, 10);
    
    if (filtered.length > 0) {
        let html = "";
        filtered.forEach(p => {
            html += `<a href="#" class="list-group-item list-group-item-action" onclick="selectProduct(${p.id}); return false;">${p.name} - Gs. ${formatMoney(p.sale_price)}</a>`;
        });
        results.innerHTML = html;
        results.style.display = "block";
    } else {
        results.style.display = "none";
    }
});

function selectProduct(id) {
    selectedProduct = allProducts.find(p => p.id === id);
    document.getElementById("searchProduct").value = selectedProduct.name;
    document.getElementById("addPrice").value = selectedProduct.sale_price;
    document.getElementById("addQty").value = 1;
    document.getElementById("searchResults").style.display = "none";
}

function addSelectedProduct() {
    if (!selectedProduct) {
        alert("Seleccione un producto primero");
        return;
    }
    
    const qty = parseInt(document.getElementById("addQty").value) || 1;
    const price = parseFloat(document.getElementById("addPrice").value) || 0;
    
    if (price <= 0) {
        alert("Ingrese un precio válido");
        return;
    }
    
    const existing = editItems.find(item => item.id === selectedProduct.id);
    if (existing) {
        existing.qty += qty;
    } else {
        editItems.push({
            id: selectedProduct.id,
            name: selectedProduct.name,
            code: selectedProduct.code,
            price: price,
            qty: qty
        });
    }
    
    renderItems();
    updateTotals();
    
    // Limpiar
    document.getElementById("searchProduct").value = "";
    document.getElementById("addQty").value = 1;
    document.getElementById("addPrice").value = 0;
    selectedProduct = null;
}

document.getElementById("updateSaleForm").addEventListener("submit", function(e) {
    if (editItems.length === 0) {
        e.preventDefault();
        alert("Debe agregar al menos un producto");
        return;
    }
    updateTotals();
});

document.addEventListener("click", function(e) {
    if (!document.getElementById("searchProduct").contains(e.target) && !document.getElementById("searchResults").contains(e.target)) {
        document.getElementById("searchResults").style.display = "none";
    }
});

// Inicializar
updateTotals();
</script>';
