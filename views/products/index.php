<?php 
$title = 'Productos';
$pageTitle = 'Gestión de Productos';

$action = $_GET['action'] ?? 'list';
$product_id = intval($_GET['id'] ?? 0);

$product = [
    'id' => 0,
    'code' => '',
    'barcode' => '',
    'name' => '',
    'description' => '',
    'category_id' => '',
    'provider_id' => '',
    'unit' => 'unidad',
    'cost_price' => 0,
    'sale_price' => 0,
    'wholesale_price' => 0,
    'iva' => 10,
    'stock' => 0,
    'min_stock' => 5,
    'location' => '',
    'image' => ''
];

if ($action === 'edit' && $product_id > 0) {
    $stmt = db()->prepare("SELECT * FROM products WHERE id = ? AND active = 1");
    $stmt->execute([$product_id]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($data) {
        $product = $data;
    }
}

// Asegurar que IVA existe en la tabla
try {
    db()->exec("ALTER TABLE products ADD COLUMN iva INTEGER DEFAULT 10");
} catch (PDOException $e) {}

// ===== PROCESAR GUARDADO =====
if ($action === 'save' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = trim($_POST['code'] ?? '');
    $barcode = trim($_POST['barcode'] ?? '');
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $category_id = intval($_POST['category_id'] ?? 0) ?: null;
    $provider_id = intval($_POST['provider_id'] ?? 0) ?: null;
    $unit = trim($_POST['unit'] ?? 'unidad');
    $cost_price = intval($_POST['cost_price'] ?? 0);
    $sale_price = intval($_POST['sale_price'] ?? 0);
    $wholesale_price = intval($_POST['wholesale_price'] ?? 0);
    $iva = intval($_POST['iva'] ?? 10);
    $stock = intval($_POST['stock'] ?? 0);
    $min_stock = intval($_POST['min_stock'] ?? 5);
    $location = trim($_POST['location'] ?? '');
    $image = $product['image'] ?? '';
    
    if (!empty($_FILES['productImageFile']['name'])) {
        $uploadDir = dirname(__DIR__, 2) . '/public/uploads/products/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        $ext = strtolower(pathinfo($_FILES['productImageFile']['name'], PATHINFO_EXTENSION));
        $allowedExts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        
        if (in_array($ext, $allowedExts)) {
            $newFileName = uniqid('prod_') . '.' . $ext;
            $fullPath = $uploadDir . $newFileName;
            
            // Intentar con move_uploaded_file, si falla usar copy
            if (isset($_FILES['productImageFile']['tmp_name']) && is_uploaded_file($_FILES['productImageFile']['tmp_name'])) {
                if (move_uploaded_file($_FILES['productImageFile']['tmp_name'], $fullPath)) {
                    $image = 'uploads/products/' . $newFileName;
                }
            } else if (isset($_FILES['productImageFile']['tmp_name']) && $_FILES['productImageFile']['tmp_name']) {
                // Para pruebas locales o cuando move_uploaded_file no funciona
                if (copy($_FILES['productImageFile']['tmp_name'], $fullPath)) {
                    $image = 'uploads/products/' . $newFileName;
                }
            }
        }
    }
    
    $product_id_form = intval($_POST['product_id'] ?? 0);
    
    if (empty($name)) {
        flash('error', 'El nombre es requerido');
        redirect('?page=products');
    }
    
    try {
        if ($product_id_form > 0) {
            // UPDATE
            $sql = "UPDATE products SET code = ?, barcode = ?, name = ?, description = ?, category_id = ?, provider_id = ?, unit = ?, cost_price = ?, sale_price = ?, wholesale_price = ?, iva = ?, stock = ?, min_stock = ?, location = ?, image = ? WHERE id = ?";
            $stmt = db()->prepare($sql);
            $success = $stmt->execute([$code, $barcode, $name, $description, $category_id, $provider_id, $unit, $cost_price, $sale_price, $wholesale_price, $iva, $stock, $min_stock, $location, $image, $product_id_form]);
            if ($success) {
                flash('success', 'Producto actualizado correctamente');
            } else {
                flash('error', 'No se pudo actualizar el producto');
            }
        } else {
            // INSERT
            $sql = "INSERT INTO products (code, barcode, name, description, category_id, provider_id, unit, cost_price, sale_price, wholesale_price, iva, stock, min_stock, location, image) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = db()->prepare($sql);
            $success = $stmt->execute([$code, $barcode, $name, $description, $category_id, $provider_id, $unit, $cost_price, $sale_price, $wholesale_price, $iva, $stock, $min_stock, $location, $image]);
            if ($success) {
                flash('success', 'Producto creado correctamente');
            } else {
                flash('error', 'No se pudo crear el producto');
            }
        }
    } catch (PDOException $e) {
        flash('error', 'Error en base de datos: ' . $e->getMessage());
    } catch (Exception $e) {
        flash('error', 'Error: ' . $e->getMessage());
    }
    redirect('?page=products');
}

// ===== PROCESAR ELIMINAR =====
if ($action === 'delete' && $product_id > 0) {
    try {
        $stmt = db()->prepare("UPDATE products SET active = 0 WHERE id = ?");
        $stmt->execute([$product_id]);
        flash('success', 'Producto eliminado correctamente');
    } catch (Exception $e) {
        flash('error', 'Error: ' . $e->getMessage());
    }
    redirect('?page=products');
}

// ===== MOSTRAR FORMULARIO =====
if ($action === 'new' || $action === 'edit') {
    if ($action === 'edit' && $product_id > 0) {
        $stmt = db()->prepare("SELECT * FROM products WHERE id = ? AND active = 1");
        $stmt->execute([$product_id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($data) {
            $product = $data;
        }
    }
    
    $categories = db()->query("SELECT * FROM categories WHERE active = 1 ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
    $providers = db()->query("SELECT * FROM providers WHERE active = 1 ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
    
    $content = '
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">' . ($action === 'new' ? 'Nuevo Producto' : 'Editar Producto') . '</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="?page=products&action=save" enctype="multipart/form-data">
                <input type="hidden" name="product_id" value="' . intval($product['id']) . '">
                
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Código</label>
                        <input type="text" name="code" class="form-control" value="' . htmlspecialchars($product['code']) . '">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Código de Barras</label>
                        <input type="text" name="barcode" class="form-control" value="' . htmlspecialchars($product['barcode']) . '">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Nombre *</label>
                        <input type="text" name="name" class="form-control" required value="' . htmlspecialchars($product['name']) . '">
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Categoría</label>
                        <select name="category_id" class="form-select">
                            <option value="">Seleccionar</option>';
    foreach ($categories as $c) {
        $selected = ($c['id'] == $product['category_id']) ? 'selected' : '';
        $content .= '<option value="' . $c['id'] . '" ' . $selected . '>' . htmlspecialchars($c['name']) . '</option>';
    }
    $content .= '</select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Proveedor</label>
                        <select name="provider_id" class="form-select">
                            <option value="">Seleccionar</option>';
    foreach ($providers as $p) {
        $selected = ($p['id'] == $product['provider_id']) ? 'selected' : '';
        $content .= '<option value="' . $p['id'] . '" ' . $selected . '>' . htmlspecialchars($p['name']) . '</option>';
    }
    $content .= '</select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Unidad</label>
                        <select name="unit" class="form-select">
                            <option value="unidad" ' . ($product['unit'] === 'unidad' ? 'selected' : '') . '>Unidad</option>
                            <option value="kilo" ' . ($product['unit'] === 'kilo' ? 'selected' : '') . '>Kilo</option>
                            <option value="metro" ' . ($product['unit'] === 'metro' ? 'selected' : '') . '>Metro</option>
                            <option value="litro" ' . ($product['unit'] === 'litro' ? 'selected' : '') . '>Litro</option>
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">IVA %</label>
                        <select name="iva" class="form-select">
                            <option value="0" ' . ($product['iva'] == 0 ? 'selected' : '') . '>Exento (0%)</option>
                            <option value="5" ' . ($product['iva'] == 5 ? 'selected' : '') . '>5%</option>
                            <option value="10" ' . ($product['iva'] == 10 ? 'selected' : '') . '>10%</option>
                        </select>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Precio Costo</label>
                        <input type="number" name="cost_price" id="costPrice" class="form-control" step="1" min="0" value="' . htmlspecialchars($product['cost_price']) . '">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Margen (%)</label>
                        <input type="number" name="margin" id="marginPercent" class="form-control" step="1" min="0" value="30" onchange="calculatePrices()">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Precio Venta</label>
                        <input type="number" name="sale_price" id="salePrice" class="form-control" step="1" min="0" value="' . htmlspecialchars($product['sale_price']) . '" onchange="calculateWholesale()">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Precio Mayorista</label>
                        <input type="number" name="wholesale_price" id="wholesalePrice" class="form-control" step="1" min="0" value="' . htmlspecialchars($product['wholesale_price']) . '">
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Stock</label>
                        <input type="number" name="stock" class="form-control" value="' . htmlspecialchars($product['stock']) . '">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Stock Mínimo</label>
                        <input type="number" name="min_stock" class="form-control" value="' . htmlspecialchars($product['min_stock']) . '">
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Imagen</label>
                    <input type="file" name="productImageFile" id="productImageFile" class="form-control" accept="image/*" onchange="previewImage(this)">
                    <input type="hidden" name="image" id="productImagePath" value="' . htmlspecialchars($product['image'] ?? '') . '">
                    <div id="imagePreview" class="mt-2">';
    if (!empty($product['image'])) {
        $content .= '<img src="' . htmlspecialchars($product['image']) . '" style="max-height: 150px; border: 1px solid #ddd; padding: 5px;">';
    }
    $content .= '</div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Descripción</label>
                    <textarea name="description" class="form-control" rows="3">' . htmlspecialchars($product['description']) . '</textarea>
                </div>
                
                <button type="submit" class="btn btn-primary">Guardar</button>
                <a href="?page=products" class="btn btn-secondary">Cancelar</a>
            </form>
            
            <script>
            function calculatePrices() {
                var cost = parseInt(document.getElementById("costPrice").value) || 0;
                var margin = parseInt(document.getElementById("marginPercent").value) || 0;
                var salePrice = Math.round(cost + (cost * margin / 100));
                document.getElementById("salePrice").value = salePrice;
                calculateWholesale();
            }
            
            function calculateWholesale() {
                var salePrice = parseInt(document.getElementById("salePrice").value) || 0;
                var wholesalePrice = Math.round(salePrice * 0.85);
                document.getElementById("wholesalePrice").value = wholesalePrice;
            }
            
            function previewImage(input) {
                if (input.files && input.files[0]) {
                    var file = input.files[0];
                    var reader = new FileReader();
                    reader.onload = function(e) {
                        document.getElementById("imagePreview").innerHTML = "<img src=\"" + e.target.result + "\" style=\"max-height: 150px; border: 1px solid #ddd; padding: 5px;\">";
                        document.getElementById("productImagePath").value = file.name;
                    };
                    reader.readAsDataURL(file);
                }
            }
            
            document.getElementById("costPrice").addEventListener("input", calculatePrices);
            </script>
        </div>
    </div>';
    return;
}

// ===== LISTAR PRODUCTOS =====
$sort = $_GET['sort'] ?? 'name';
$orderBy = $sort === 'stock' ? 'p.stock ASC, p.min_stock DESC' : 'p.name';
$products = db()->query("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.active = 1 ORDER BY $orderBy")->fetchAll(PDO::FETCH_ASSOC);

$rows_html = '';
foreach ($products as $p) {
    $stockClass = $p['stock'] <= $p['min_stock'] ? 'text-danger fw-bold' : '';
    $iva_label = $p['iva'] == 0 ? 'Exento' : $p['iva'] . '%';
    $imgHtml = !empty($p['image']) ? '<img src="' . BASE_URL . '/' . htmlspecialchars($p['image']) . '" style="max-height: 50px;">' : '';
    $rows_html .= '<tr>
        <td>' . $imgHtml . '</td>
        <td>' . htmlspecialchars($p['code']) . '</td>
        <td>' . htmlspecialchars($p['name']) . '</td>
        <td>' . ($p['category_name'] ?? '-') . '</td>
        <td>' . Format::money($p['sale_price']) . '</td>
        <td><span class="badge bg-secondary">' . $iva_label . '</span></td>
        <td class="' . $stockClass . '">' . $p['stock'] . ' ' . $p['unit'] . '</td>
        <td>
            <a href="?page=products&action=edit&id=' . $p['id'] . '" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
            <a href="?page=products&action=delete&id=' . $p['id'] . '" class="btn btn-sm btn-outline-danger" onclick="return confirm(\'Confirmar?\')"><i class="bi bi-trash"></i></a>
        </td>
    </tr>';
}

$content = '
<div class="mb-4">
    <a href="?page=dashboard" class="btn btn-nav-back me-2"><i class="bi bi-arrow-left"></i> Volver al Dashboard</a>
</div>

<h4 class="mb-4"><i class="bi bi-box"></i> Gestión de Productos</h4>

<div class="row mb-4">
    <div class="col-md-6">
        <div class="card" style="border-top: 5px solid var(--primary); background: linear-gradient(135deg, rgba(37, 99, 235, 0.08), transparent);">
            <div class="card-body">
                <h6 class="text-primary mb-1"><i class="bi bi-box2-heart"></i> Total de Productos</h6>
                <h3 class="mb-0 text-primary" style="font-size: 2rem; font-weight: 700;">' . count($products) . '</h3>
                <a href="?page=products&action=new" class="btn btn-sm btn-primary mt-3"><i class="bi bi-plus-circle"></i> Nuevo Producto</a>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card" style="border-top: 5px solid var(--warning); background: linear-gradient(135deg, rgba(245, 158, 11, 0.08), transparent);">
            <div class="card-body">
                <h6 class="text-warning mb-1"><i class="bi bi-search"></i> Buscador Rápido</h6>
                <input type="text" id="search" class="form-control" placeholder="Buscar por código, nombre o categoría..." style="margin-top: 10px;">
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header bg-light">
        <h6 class="mb-0"><i class="bi bi-table"></i> Detalle de Productos</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0" id="tabla">
                <thead class="table-light">
                    <tr>
                        <th>Img</th>
                        <th>Código</th>
                        <th>Nombre</th>
                        <th>Categoría</th>
                        <th>Precio Venta</th>
                        <th>IVA</th>
                        <th>Stock</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    ' . $rows_html . '
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
document.getElementById("search").addEventListener("keyup", function() {
    var filter = this.value.toLowerCase();
    document.querySelectorAll("#tabla tbody tr").forEach(function(row) {
        row.style.display = row.textContent.toLowerCase().includes(filter) ? "" : "none";
    });
});
</script>';
