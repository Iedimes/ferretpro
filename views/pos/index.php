<?php 
$title = 'Punto de Venta';
$pageTitle = 'Punto de Venta';

$categories = db()->query("SELECT * FROM categories WHERE active = 1 ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
$clients = db()->query("SELECT * FROM clients WHERE active = 1 ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
$products = db()->query("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.active = 1 AND p.stock > 0 ORDER BY p.name")->fetchAll(PDO::FETCH_ASSOC);

$content = '
<div class="row">
    <div class="col-md-8">
        <div class="card mb-3">
            <div class="card-header">
                <div class="row">
                    <div class="col-md-6">
                        <input type="text" id="searchProduct" class="form-control" placeholder="Buscar producto por código, nombre o barcode..." autocomplete="off">
                        <div id="searchResults" class="list-group position-absolute" style="z-index: 1000; max-height: 300px; overflow-y: auto; display: none; width: 100%; top: 38px;"></div>
                    </div>
                    <div class="col-md-3">
                        <select id="filterCategory" class="form-select">
                            <option value="">Todas las categorías</option>
                            ' . implode('', array_map(fn($c) => "<option value=\"{$c['id']}\">{$c['name']}</option>", $categories)) . '
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button type="button" class="btn btn-outline-secondary w-100" id="btnActualizar">
                            <i class="bi bi-arrow-clockwise"></i> Actualizar
                        </button>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div id="productsGrid" class="row">';

foreach ($products as $p) {
    $stock_warning = $p['stock'] <= $p['min_stock'] ? 'border-warning border-2' : '';
    $iva_label = $p['iva'] == 0 ? 'Exento' : $p['iva'] . '%';
    $content .= '<div class="col-md-3 col-6 mb-2 product-card" data-category="' . ($p['category_id'] ?? '') . '" data-min-stock="' . $p['min_stock'] . '">
        <div class="card h-100 product-item ' . $stock_warning . '" style="cursor: pointer;" data-id="' . $p['id'] . '" data-name="' . htmlspecialchars($p['name']) . '" data-price="' . $p['sale_price'] . '" data-wholesale-price="' . ($p['wholesale_price'] ?? $p['sale_price']) . '" data-stock="' . $p['stock'] . '" data-min-stock="' . $p['min_stock'] . '" data-code="' . htmlspecialchars($p['code']) . '" data-iva="' . $p['iva'] . '">
            <div class="card-body text-center p-2">
                <h6 class="mb-1 text-truncate">' . htmlspecialchars($p['name']) . '</h6>
                <p class="text-muted small mb-1">' . $p['code'] . '</p>
                <h5 class="text-primary mb-1">' . Format::money($p['sale_price']) . '</h5>
                <small class="text-muted">Stock: ' . $p['stock'] . '</small>';
    
    if ($p['stock'] <= $p['min_stock']) {
        $content .= '<br><small class="text-warning fw-bold">⚠️ Stock bajo (mín: ' . $p['min_stock'] . ')</small>';
    }
    
    $content .= '<br><small class="badge bg-secondary">' . $iva_label . '</small>';
    
    $content .= '</div>
        </div>
    </div>';
}

$content .= '</div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Carrito</h5>
                <button type="button" class="btn btn-sm btn-outline-danger" id="btnLimpiar">Limpiar</button>
            </div>
            <div class="card-body p-0">
                <div id="cartItems" style="max-height: 400px; overflow-y: auto;">
                    <p class="text-center text-muted p-3">Carrito vacío</p>
                </div>
            </div>
            <div class="card-footer">
                <div class="mb-2">
                    <label class="form-label">Cliente (opcional)</label>
                    <div class="position-relative">
                        <div class="input-group input-group-sm">
                            <input type="text" id="clientSearch" class="form-control" placeholder="Buscar por nombre o RUC..." autocomplete="off">
                            <button type="button" class="btn btn-outline-primary" onclick="window.location.href=\'?page=clients&action=new\'" title="Nuevo cliente">
                                <i class="bi bi-plus-circle"></i>
                            </button>
                        </div>
                        <div id="clientResults" class="list-group position-absolute w-100" style="z-index: 1000; max-height: 200px; overflow-y: auto; display: none;"></div>
                    </div>
                    <select name="client_id" id="clientSelect" class="form-select form-select-sm mt-1" style="display: none;">
                        <option value="" data-balance="0" data-category="minorista">Mostrador</option>
                         ' . implode('', array_map(fn($c) => "<option value=\"{$c['id']}\" data-balance=\"{$c['balance']}\" data-document=\"{$c['document']}\" data-category=\"{$c['category']}\">{$c['name']} - {$c['document']} " . ($c['balance'] > 0 ? "(Deuda: " . Format::money($c['balance']) . ")" : "") . "</option>", $clients)) . '
                    </select>
                    <div class="form-check mt-2">
                        <input type="checkbox" class="form-check-input" id="wholesaleDiscount" onchange="renderCart()">
                        <label class="form-check-label" for="wholesaleDiscount">Aplicar descuento mayorista (15%)</label>
                    </div>
                </div>
                <div class="mb-2">
                    <label class="form-label">Tipo de Venta</label>
                    <select name="sale_type" id="saleType" class="form-select form-select-sm" onchange="checkSaleType()">
                        <option value="contado">Contado</option>
                        <option value="credito">Crédito (Anotar)</option>
                        <option value="mayorista">Mayorista</option>
                    </select>
                </div>
                <div class="mb-2">
                    <label class="form-label">Método de Pago</label>
                    <select name="payment_method" id="paymentMethod" class="form-select form-select-sm">
                        <option value="efectivo">Efectivo</option>
                        <option value="transferencia">Transferencia</option>
                        <option value="tarjeta">Tarjeta</option>
                        <option value="mixto">Mixto</option>
                    </select>
                </div>
                <div class="mb-2">
                    <label class="form-label">Descuento (%)</label>
                    <input type="number" id="globalDiscount" class="form-control form-select-sm" value="0" min="0" max="100">
                </div>
                <div class="mb-2">
                    <label class="form-label">Entrega</label>
                    <select name="delivery_type" id="deliveryType" class="form-select form-select-sm">
                        <option value="mostrador">Mostrador</option>
                        <option value="delivery">Delivery</option>
                        <option value="pendiente">Pendiente (Retirar luego)</option>
                    </select>
                </div>
                 <hr>
                  <div class="d-flex justify-content-between mb-1" style="font-size: 0.9rem;">
                      <span>Subtotal:</span>
                      <span id="cartSubtotal">Gs. 0</span>
                  </div>
                  <div class="d-flex justify-content-between mb-1" style="font-size: 0.9rem;">
                      <span>IVA:</span>
                      <span id="cartIVA">Gs. 0</span>
                  </div>
                  <div class="d-flex justify-content-between mb-2" style="font-size: 0.9rem;">
                      <span>Descuento:</span>
                      <span id="cartDiscount">Gs. 0</span>
                  </div>
                  <div class="d-flex justify-content-between mb-2">
                      <span><strong>Total:</strong></span>
                      <span id="cartTotal"><strong>Gs. 0</strong></span>
                  </div>
                <button type="button" class="btn btn-success w-100" id="btnFinalizarVenta">
                    <i class="bi bi-check-circle"></i> Finalizar Venta
                </button>
            </div>
        </div>
    </div>
</div>

<form id="saleForm" method="POST" action="?page=pos_process" style="display: none;">
    <input type="hidden" name="items" id="saleItems">
    <input type="hidden" name="client_id" id="saleClientId">
    <input type="hidden" name="sale_type" id="saleTypeVal">
    <input type="hidden" name="payment_method" id="salePaymentMethod">
    <input type="hidden" name="discount" id="saleDiscount">
    <input type="hidden" name="delivery_type" id="saleDeliveryType">
    <input type="hidden" name="wholesale_discount" id="saleWholesaleDiscount">
</form>

<script>
console.log("🔧 Script POS iniciando...");

// Funciones globales
let posCart = [];
let currentPriceType = "sale"; // "sale" or "wholesale"

// Función para formatear número con punto como separador de miles
function formatMoney(num) {
    return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
}

// ========== TIPO DE VENTA ==========
function checkSaleType() {
    const saleType = document.getElementById("saleType").value;
    const clientSelect = document.getElementById("clientSelect");
    
    // Validar tipo de venta
    if (saleType === "credito" && !clientSelect.value) {
        alert("Debe seleccionar un cliente para crédito");
        document.getElementById("saleType").value = "contado";
        return;
    }
    
    // Cambiar precios según tipo
    currentPriceType = (saleType === "mayorista") ? "wholesale" : "sale";
    
    // Actualizar precios en las tarjetas de productos
    document.querySelectorAll(".product-item").forEach(item => {
        const basePrice = parseFloat(item.dataset.price);
        const wholesalePrice = parseFloat(item.dataset.wholesalePrice);
        
        if (currentPriceType === "wholesale" && wholesalePrice > 0) {
            item.dataset.price = wholesalePrice;
            // Actualizar el precio mostrado en la tarjeta
            const priceEl = item.querySelector("h5");
            if (priceEl) {
                priceEl.textContent = "Gs. " + formatMoney(wholesalePrice) + " (M)";
            }
        } else {
            item.dataset.price = basePrice;
            const priceEl = item.querySelector("h5");
            if (priceEl) {
                priceEl.textContent = "Gs. " + formatMoney(basePrice);
            }
        }
    });
    
    // Actualizar precios en el carrito
    renderCart();
    
    console.log("📦 Tipo de precio:", currentPriceType);
}

// ========== FUNCIONES CARRITO ==========
function renderCart() {
    const container = document.getElementById("cartItems");
    if (posCart.length === 0) {
        container.innerHTML = "<p class=\"text-center text-muted p-3\">Carrito vacío</p>";
        document.getElementById("cartSubtotal").textContent = "Gs. 0";
        document.getElementById("cartIVA").textContent = "Gs. 0";
        document.getElementById("cartDiscount").textContent = "Gs. 0";
        document.getElementById("cartTotal").innerHTML = "<strong>Gs. 0</strong>";
        return;
    }
    
    const discount = parseFloat(document.getElementById("globalDiscount").value) || 0;
    const wholesaleDiscount = document.getElementById("wholesaleDiscount").checked;
    const wholesalePct = wholesaleDiscount ? 0.15 : 0;
    
    let subtotal = 0;
    let totalIVA = 0;
    let html = "<table class=\"table table-sm mb-0\"><tbody>";
    
    posCart.forEach(item => {
        let itemPrice = item.price;
        if (wholesalePct > 0) {
            itemPrice = itemPrice * (1 - wholesalePct);
        }
        const itemTotal = itemPrice * item.qty;
        const itemIVA = item.iva === 10 ? Math.round(itemTotal / 11) : (item.iva === 5 ? Math.round(itemTotal / 21) : 0);
        const itemBase = itemTotal - itemIVA;
        subtotal += itemBase;
        totalIVA += itemIVA;
        
        const ivaLabel = item.iva === 0 ? "Ex" : item.iva + "%";
        html += "<tr><td>" + item.name + " <small class=\"badge bg-secondary\">" + ivaLabel + "</small></td><td style=\"width: 80px;\"><input type=\"number\" class=\"form-control form-control-sm\" value=\"" + item.qty + "\" min=\"1\" max=\"" + item.stock + "\" onchange=\"updateItemQty(" + JSON.stringify(item.id) + ", parseInt(this.value))\"></td><td class=\"text-end\" style=\"font-size: 0.85rem;\">Gs. " + formatMoney(itemTotal) + "</td><td style=\"width: 30px;\"><button class=\"btn btn-sm btn-outline-danger\" type=\"button\" onclick=\"removeItem(" + JSON.stringify(item.id) + ")\"><i class=\"bi bi-x\"></i></button></td></tr>";
    });
    html += "</tbody></table>";
    container.innerHTML = html;
    
    const discountAmount = subtotal * (discount / 100);
    const total = subtotal + totalIVA - discountAmount;
    
    document.getElementById("cartSubtotal").textContent = "Gs. " + formatMoney(subtotal);
    document.getElementById("cartIVA").textContent = "Gs. " + formatMoney(totalIVA);
    document.getElementById("cartDiscount").textContent = "Gs. " + formatMoney(discountAmount);
    document.getElementById("cartTotal").innerHTML = "<strong>Gs. " + formatMoney(total) + "</strong>";
}

function addItem(product) {
    console.log("📦 Agregando producto:", product);
    const existing = posCart.find(p => p.id === product.id);
    if (existing) {
        if (existing.qty < product.stock) {
            existing.qty++;
        } else {
            alert("⚠️ Stock insuficiente\n\nProducto: " + product.name + "\nStock disponible: " + product.stock + "\nCantidad en carrito: " + existing.qty);
            return;
        }
    } else {
        posCart.push({ 
            id: product.id, 
            name: product.name, 
            price: product.price, 
            stock: product.stock, 
            iva: product.iva, 
            qty: 1 
        });
    }
    renderCart();
}

function updateItemQty(id, qty) {
    console.log("📝 Actualizando cantidad:", id, qty);
    id = parseInt(id); // Convertir a número
    const item = posCart.find(p => p.id === id);
    if (item) {
        if (qty <= 0) {
            posCart = posCart.filter(p => p.id !== id);
        } else if (qty <= item.stock) {
            item.qty = qty;
        } else {
            alert("⚠️ Stock insuficiente\n\nProducto: " + item.name + "\nStock disponible: " + item.stock + "\nIntentó: " + qty);
            // Revertir a la cantidad anterior
            renderCart();
            return;
        }
    }
    renderCart();
}

function removeItem(id) {
    console.log("❌ Removiendo producto:", id);
    id = parseInt(id); // Convertir a número
    posCart = posCart.filter(p => p.id !== id);
    renderCart();
}

function clearCartItems() {
    console.log("🗑️ Limpiando carrito");
    posCart = [];
    renderCart();
}

// ========== FUNCIONES BÚSQUEDA Y FILTRADO ==========
function loadProductsByCategory() {
    const category = document.getElementById("filterCategory").value;
    console.log("🔍 Filtrando por categoría:", category);
    const cards = document.querySelectorAll(".product-card");
    let shown = 0;
    cards.forEach(card => {
        if (!category || card.dataset.category == category) {
            card.style.display = "";
            shown++;
        } else {
            card.style.display = "none";
        }
    });
    console.log("✓ Mostrando", shown, "productos");
}

function searchProducts() {
    const searchInput = document.getElementById("searchProduct");
    const searchResults = document.getElementById("searchResults");
    const term = (searchInput.value || "").toLowerCase().trim();
    
    console.log("🔎 Buscando:", term);
    
    if (term.length < 2) {
        searchResults.style.display = "none";
        return;
    }
    
    const products = document.querySelectorAll(".product-item");
    const results = [];
    
    products.forEach(p => {
        const name = (p.dataset.name || "").toLowerCase();
        const code = (p.dataset.code || "").toLowerCase();
        if (name.includes(term) || code.includes(term)) {
            results.push(p);
        }
    });
    
    console.log("✓ Encontrados", results.length, "resultados");
    
    if (results.length > 0) {
        let html = "";
        results.slice(0, 10).forEach(p => {
            html += "<a href=\"#\" class=\"list-group-item list-group-item-action\" onclick=\"selectFromSearch(\'" + p.dataset.id + "\'); return false;\">" + 
                p.dataset.name + " - Gs. " + formatMoney(parseFloat(p.dataset.price)) + 
                "</a>";
        });
        searchResults.innerHTML = html;
        searchResults.style.display = "block";
    } else {
        searchResults.style.display = "none";
    }
}

function selectFromSearch(id) {
    console.log("✨ Seleccionado de búsqueda:", id);
    const item = document.querySelector(".product-item[data-id=\"" + id + "\"]");
     if (item) {
         addItem({
             id: parseInt(item.dataset.id),
             name: item.dataset.name,
             price: parseFloat(item.dataset.price),
             stock: parseInt(item.dataset.stock),
             iva: parseInt(item.dataset.iva)
         });
     }
    document.getElementById("searchProduct").value = "";
    document.getElementById("searchResults").style.display = "none";
}

// ========== PROCESAR VENTA ==========
function processSale() {
    console.log("💳 Procesando venta...");
    if (posCart.length === 0) {
        alert("Agregue productos al carrito");
        return;
    }
    
    const clientSelect = document.getElementById("clientSelect");
    const saleType = document.getElementById("saleType").value;
    const clientId = clientSelect.value;
    const clientBalance = clientSelect.options[clientSelect.selectedIndex]?.dataset.balance || 0;
    
    if (saleType === "credito" && !clientId) {
        alert("Debe seleccionar un cliente para crédito");
        return;
    }
    
    if (clientBalance > 0) {
        if (!confirm("El cliente tiene una deuda pendiente de Gs. " + formatMoney(parseFloat(clientBalance)) + ". ¿Continuar?")) {
            return;
        }
    }
    
    document.getElementById("saleItems").value = JSON.stringify(posCart);
    document.getElementById("saleClientId").value = clientId;
    document.getElementById("saleTypeVal").value = saleType;
    document.getElementById("salePaymentMethod").value = document.getElementById("paymentMethod").value;
    document.getElementById("saleDiscount").value = document.getElementById("globalDiscount").value;
    document.getElementById("saleDeliveryType").value = document.getElementById("deliveryType").value;
    document.getElementById("saleWholesaleDiscount").value = document.getElementById("wholesaleDiscount").checked ? "1" : "0";
    
    console.log("✓ Enviando venta...");
    document.getElementById("saleForm").submit();
}

// ========== SELECCIÓN DE CLIENTE ==========
function selectClient(id) {
    var select = document.getElementById("clientSelect");
    select.value = id;
    var selectedOption = select.options[select.selectedIndex];
    var clientName = selectedOption ? selectedOption.text : "";
    var clientCategory = selectedOption ? selectedOption.getAttribute("data-category") : "minorista";
    
    document.getElementById("clientSearch").value = clientName;
    document.getElementById("clientSearch").readOnly = true;
    document.getElementById("clientResults").style.display = "none";
    select.style.display = "none";
    
    // Auto-check mayorista discount
    var wholesaleCheck = document.getElementById("wholesaleDiscount");
    wholesaleCheck.checked = (clientCategory === "mayorista");
    renderCart();
}

// ========== INICIALIZACIÓN ==========
document.addEventListener("DOMContentLoaded", function() {
    console.log("✅ DOM LOADED - Inicializando listeners...");
    
    // Botones principales
    const btnActualizar = document.getElementById("btnActualizar");
    const btnLimpiar = document.getElementById("btnLimpiar");
    const btnFinalizarVenta = document.getElementById("btnFinalizarVenta");
    
    if (btnActualizar) {
        btnActualizar.addEventListener("click", function(e) {
            e.preventDefault();
            console.log("👆 Clic en Actualizar");
            loadProductsByCategory();
        });
    }
    
    // Búsqueda de cliente
    const clientSearch = document.getElementById("clientSearch");
    const clientResults = document.getElementById("clientResults");
    const clientSelect = document.getElementById("clientSelect");
    if (clientSearch && clientResults && clientSelect) {
        clientSearch.addEventListener("input", function(e) {
            var term = this.value.toLowerCase().trim();
            if (term.length < 1) {
                clientResults.style.display = "none";
                return;
            }
            
            var html = "";
            for (var i = 0; i < clientSelect.options.length; i++) {
                var option = clientSelect.options[i];
                var text = option.text.toLowerCase();
                var doc = (option.getAttribute("data-document") || "").toLowerCase();
                if (text.indexOf(term) !== -1 || doc.indexOf(term) !== -1) {
                    var clientId = option.value;
                    var clientName = option.text;
                    html += "<a href=\"#\" class=\"list-group-item list-group-item-action py-1\" onclick=\"selectClient(" + clientId + "); return false;\">" + clientName + "</a>";
                }
            }
            
            if (html) {
                clientResults.innerHTML = html;
                clientResults.style.display = "block";
            } else {
                clientResults.style.display = "none";
            }
        });
    }
    
    // Limpiar cliente al hacer click en el input
    if (clientSearch) {
        clientSearch.addEventListener("click", function() {
            if (this.readOnly) {
                this.value = "";
                this.readOnly = false;
                var select = document.getElementById("clientSelect");
                select.value = "";
                select.style.display = "none";
            }
        });
    }
    
    if (btnLimpiar) {
        btnLimpiar.addEventListener("click", function(e) {
            e.preventDefault();
            console.log("👆 Clic en Limpiar");
            clearCartItems();
        });
    }
    
    if (btnFinalizarVenta) {
        btnFinalizarVenta.addEventListener("click", function(e) {
            e.preventDefault();
            console.log("👆 Clic en Finalizar");
            processSale();
        });
    }
    
    // Categoría
    const filterCategory = document.getElementById("filterCategory");
    if (filterCategory) {
        filterCategory.addEventListener("change", function(e) {
            console.log("👆 Cambio de categoría");
            loadProductsByCategory();
        });
    }
    
    // Búsqueda
    const searchInput = document.getElementById("searchProduct");
    if (searchInput) {
        searchInput.addEventListener("input", searchProducts);
        searchInput.addEventListener("keypress", function(e) {
            if (e.key === "Enter") {
                e.preventDefault();
                console.log("⌨️ Enter en búsqueda");
                searchProducts();
            }
        });
    }
    
    // Descuento
    const globalDiscount = document.getElementById("globalDiscount");
    if (globalDiscount) {
        globalDiscount.addEventListener("input", renderCart);
    }
    
    // Tipo de venta
    const saleType = document.getElementById("saleType");
    if (saleType) {
        saleType.addEventListener("change", function() {
            const paymentMethod = document.getElementById("paymentMethod");
            if (this.value === "credito") {
                paymentMethod.disabled = true;
                paymentMethod.value = "credito";
            } else {
                paymentMethod.disabled = false;
            }
        });
    }
    
    // Productos (click para agregar)
    document.querySelectorAll(".product-item").forEach(item => {
        item.addEventListener("click", function(e) {
             e.preventDefault();
             addItem({
                 id: parseInt(this.dataset.id),
                 name: this.dataset.name,
                 price: parseFloat(this.dataset.price),
                 stock: parseInt(this.dataset.stock),
                 iva: parseInt(this.dataset.iva)
             });
        });
    });
    
    // Cerrar búsqueda al hacer click afuera
    const searchResults = document.getElementById("searchResults");
    document.addEventListener("click", function(e) {
        if (searchInput && searchResults && !searchInput.contains(e.target) && !searchResults.contains(e.target)) {
            searchResults.style.display = "none";
        }
    });
    
    console.log("✅✅✅ POS INICIALIZADO CORRECTAMENTE ✅✅✅");
});
</script>
';
