<?php 
$title = 'Clientes';
$pageTitle = 'Gestión de Clientes';

$action = $_GET['action'] ?? 'list';
$client_id = $_GET['id'] ?? null;

if ($action === 'save' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $document_type = $_POST['document_type'] ?? 'CI';
    $document = $_POST['document'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $email = $_POST['email'] ?? '';
    $address = $_POST['address'] ?? '';
    $credit_limit = floatval($_POST['credit_limit'] ?? 0);
    $credit_days = intval($_POST['credit_days'] ?? 30);
    $category = $_POST['category'] ?? 'minorista';
    $client_id_form = intval($_POST['client_id'] ?? 0);
    
    try {
        if ($client_id_form > 0) {
            $stmt = db()->prepare("UPDATE clients SET name=?, document_type=?, document=?, phone=?, email=?, address=?, credit_limit=?, credit_days=?, category=? WHERE id=?");
            $stmt->execute([$name, $document_type, $document, $phone, $email, $address, $credit_limit, $credit_days, $category, $client_id_form]);
            flash('success', 'Cliente actualizado correctamente');
        } else {
            $stmt = db()->prepare("INSERT INTO clients (name, document_type, document, phone, email, address, credit_limit, credit_days, category) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$name, $document_type, $document, $phone, $email, $address, $credit_limit, $credit_days, $category]);
            flash('success', 'Cliente creado correctamente');
        }
    } catch (Exception $e) {
        flash('error', 'Error: ' . $e->getMessage());
    }
    redirect('?page=clients');
}

if ($action === 'delete' && $client_id) {
    $stmt = db()->prepare("UPDATE clients SET active = 0 WHERE id = ?");
    $stmt->execute([$client_id]);
    flash('success', 'Cliente eliminado');
    redirect('?page=clients');
}

if ($action === 'new' || $action === 'edit') {
    $client = ['id' => '', 'name' => '', 'document_type' => 'CI', 'document' => '', 'phone' => '', 'email' => '', 'address' => '', 'credit_limit' => 0, 'credit_days' => 30, 'category' => 'minorista'];
    if ($action === 'edit' && $client_id) {
        $stmt = db()->prepare("SELECT * FROM clients WHERE id = ?");
        $stmt->execute([$client_id]);
        $client = array_merge($client, $stmt->fetch(PDO::FETCH_ASSOC));
    }
    
    $content = '
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">' . ($action === 'new' ? 'Nuevo Cliente' : 'Editar Cliente') . '</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="?page=clients&action=save">
                <input type="hidden" name="client_id" value="' . intval($client['id']) . '">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Nombre *</label>
                        <input type="text" name="name" class="form-control" required value="' . htmlspecialchars($client['name']) . '">
                    </div>
                    <div class="col-md-3 mb-3">
                         <label class="form-label">Tipo Documento</label>
                         <select name="document_type" class="form-select">
                             <option value="CI" ' . ($client['document_type'] === 'CI' ? 'selected' : '') . '>CI (Cédula de Identidad)</option>
                             <option value="RUC" ' . ($client['document_type'] === 'RUC' ? 'selected' : '') . '>RUC (Registro Único del Contribuyente)</option>
                         </select>
                     </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Número</label>
                        <input type="text" name="document" class="form-control" value="' . htmlspecialchars($client['document']) . '">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Teléfono</label>
                        <input type="text" name="phone" class="form-control" value="' . htmlspecialchars($client['phone']) . '">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" value="' . htmlspecialchars($client['email']) . '">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Categoría</label>
                        <select name="category" class="form-select">
                            <option value="minorista" ' . ($client['category'] === 'minorista' ? 'selected' : '') . '>Minorista</option>
                            <option value="mayorista" ' . ($client['category'] === 'mayorista' ? 'selected' : '') . '>Mayorista</option>
                            <option value="revendedor" ' . ($client['category'] === 'revendedor' ? 'selected' : '') . '>Revendedor</option>
                        </select>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Límite de Crédito (Gs.)</label>
                        <input type="number" name="credit_limit" class="form-control" step="1" min="0" value="' . intval($client['credit_limit']) . '">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Días de Crédito</label>
                        <input type="number" name="credit_days" class="form-control" min="1" value="' . intval($client['credit_days']) . '">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Categoría</label>
                        <select name="category" class="form-select">
                            <option value="minorista" ' . ($client['category'] === 'minorista' ? 'selected' : '') . '>Minorista</option>
                            <option value="mayorista" ' . ($client['category'] === 'mayorista' ? 'selected' : '') . '>Mayorista</option>
                            <option value="revendedor" ' . ($client['category'] === 'revendedor' ? 'selected' : '') . '>Revendedor</option>
                        </select>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Guardar</button>
                <a href="?page=clients" class="btn btn-secondary">Cancelar</a>
            </form>
        </div>
    </div>';
    
    return;
}

$clients = db()->query("SELECT * FROM clients WHERE active = 1 ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
$debtors = db()->query("SELECT * FROM clients WHERE active = 1 AND balance > 0 ORDER BY balance DESC")->fetchAll(PDO::FETCH_ASSOC);

$content = '
<div class="mb-4">
    <a href="?page=dashboard" class="btn btn-nav-back me-2"><i class="bi bi-arrow-left"></i> Volver al Dashboard</a>
</div>

<h4 class="mb-4"><i class="bi bi-people"></i> Gestión de Clientes</h4>

<div class="row mb-4">
    <div class="col-md-4">
        <div class="card" style="border-top: 5px solid var(--primary); background: linear-gradient(135deg, rgba(37, 99, 235, 0.08), transparent);">
            <div class="card-body">
                <h6 class="text-primary mb-1"><i class="bi bi-people"></i> Total de Clientes</h6>
                <h3 class="mb-0 text-primary" style="font-size: 2rem; font-weight: 700;">' . count($clients) . '</h3>
                <a href="?page=clients&action=new" class="btn btn-sm btn-primary mt-3"><i class="bi bi-person-plus"></i> Nuevo Cliente</a>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card" style="border-top: 5px solid var(--danger); background: linear-gradient(135deg, rgba(239, 68, 68, 0.08), transparent);">
            <div class="card-body">
                <h6 class="text-danger mb-1"><i class="bi bi-exclamation-circle"></i> Clientes con Deuda</h6>
                <h3 class="mb-0 text-danger" style="font-size: 2rem; font-weight: 700;">' . count($debtors) . '</h3>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card" style="border-top: 5px solid var(--warning); background: linear-gradient(135deg, rgba(245, 158, 11, 0.08), transparent);">
            <div class="card-body">
                <h6 class="text-warning mb-1"><i class="bi bi-search"></i> Buscador Rápido</h6>
                <input type="text" id="searchClients" class="form-control" placeholder="Buscar por nombre..." style="margin-top: 10px;">
            </div>
        </div>
    </div>
</div>';

if (count($debtors) > 0) {
    $content .= '<div class="card mb-4" style="border-top: 5px solid var(--danger);">
    <div class="card-body" style="background: linear-gradient(135deg, rgba(239, 68, 68, 0.08), transparent);">
        <h6 class="text-danger mb-3"><i class="bi bi-exclamation-triangle"></i> <strong>' . count($debtors) . ' Cliente(s) con Deuda</strong></h6>
        <div class="table-responsive">
            <table class="table table-sm mb-0">
                <thead class="table-light"><tr><th>Cliente</th><th>Deuda</th><th>Acción</th></tr></thead>
                <tbody>';
    foreach ($debtors as $d) {
        $content .= '<tr><td><strong>' . htmlspecialchars($d['name']) . '</strong></td><td><span class="badge bg-danger">' . Format::money($d['balance']) . '</span></td><td><a href="?page=receivable&client=' . $d['id'] . '" class="btn btn-sm btn-danger"><i class="bi bi-cash-circle"></i> Cobrar</a></td></tr>';
    }
    $content .= '</tbody></table>
        </div>
    </div>
</div>';
}

$content .= '
<div class="card">
    <div class="card-header bg-light">
        <h6 class="mb-0"><i class="bi bi-table"></i> Detalle de Clientes</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0" id="clientsTable">
                <thead class="table-light">
                    <tr>
                        <th>Nombre</th>
                        <th>Documento</th>
                        <th>Teléfono</th>
                        <th>Categoría</th>
                        <th>Límite Crédito</th>
                        <th>Saldo Disponible</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>';
foreach ($clients as $c) {
    $availableCredit = $c['credit_limit'] - $c['balance'];
    $balanceClass = $availableCredit < 0 ? 'text-danger fw-bold' : 'text-success fw-bold';
    $content .= '<tr>
        <td>' . htmlspecialchars($c['name']) . '</td>
        <td>' . $c['document_type'] . ' ' . htmlspecialchars($c['document']) . '</td>
        <td>' . htmlspecialchars($c['phone']) . '</td>
        <td><span class="badge bg-secondary">' . $c['category'] . '</span></td>
        <td>' . Format::money($c['credit_limit']) . '</td>
        <td class="' . $balanceClass . '">' . Format::money($availableCredit) . '</td>
        <td>
            <a href="?page=clients&action=edit&id=' . $c['id'] . '" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
            <a href="?page=clients&action=delete&id=' . $c['id'] . '" class="btn btn-sm btn-outline-danger" onclick="return confirm(\'¿Eliminar?\')"><i class="bi bi-trash"></i></a>
        </td>
    </tr>';
}
$content .= '</tbody>
        </table>
    </div>
</div>

<script>
document.getElementById("searchClients").addEventListener("input", function() {
    const term = this.value.toLowerCase();
    const rows = document.querySelectorAll("#clientsTable tbody tr");
    rows.forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(term) ? "" : "none";
    });
});
</script>';


