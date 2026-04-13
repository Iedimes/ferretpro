<?php 
$title = 'Proveedores';
$pageTitle = 'Gestión de Proveedores';

$action = $_GET['action'] ?? 'list';
$edit_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Asegurar que RUC existe
try {
    db()->exec("ALTER TABLE providers ADD COLUMN ruc TEXT");
} catch (PDOException $e) {}

// ===== PROCESAR GUARDADO =====
if ($action === 'save' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $ruc = trim($_POST['ruc'] ?? '');
    $contact_name = trim($_POST['contact_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $notes = trim($_POST['notes'] ?? '');
    $provider_id = intval($_POST['provider_id'] ?? 0);
    
    if (empty($name)) {
        flash('error', 'El nombre es requerido');
        redirect('?page=providers');
    }
    
    try {
        if ($provider_id > 0) {
            // UPDATE
            $sql = "UPDATE providers SET name = ?, ruc = ?, contact_name = ?, phone = ?, email = ?, address = ?, notes = ? WHERE id = ?";
            $stmt = db()->prepare($sql);
            $success = $stmt->execute([$name, $ruc, $contact_name, $phone, $email, $address, $notes, $provider_id]);
            if ($success) {
                flash('success', 'Proveedor actualizado correctamente');
            } else {
                flash('error', 'No se pudo actualizar el proveedor');
            }
        } else {
            // INSERT
            $sql = "INSERT INTO providers (name, ruc, contact_name, phone, email, address, notes) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = db()->prepare($sql);
            $success = $stmt->execute([$name, $ruc, $contact_name, $phone, $email, $address, $notes]);
            if ($success) {
                flash('success', 'Proveedor creado correctamente');
            } else {
                flash('error', 'No se pudo crear el proveedor');
            }
        }
    } catch (PDOException $e) {
        flash('error', 'Error en base de datos: ' . $e->getMessage());
    } catch (Exception $e) {
        flash('error', 'Error: ' . $e->getMessage());
    }
    redirect('?page=providers');
}

// ===== PROCESAR ELIMINAR =====
if ($action === 'delete' && $edit_id > 0) {
    try {
        $stmt = db()->prepare("UPDATE providers SET active = 0 WHERE id = ?");
        $stmt->execute([$edit_id]);
        flash('success', 'Proveedor eliminado correctamente');
    } catch (Exception $e) {
        flash('error', 'Error: ' . $e->getMessage());
    }
    redirect('?page=providers');
}

// ===== MOSTRAR FORMULARIO =====
if ($action === 'new' || $action === 'edit') {
    $provider = [
        'id' => 0,
        'name' => '',
        'ruc' => '',
        'contact_name' => '',
        'phone' => '',
        'email' => '',
        'address' => '',
        'notes' => ''
    ];
    
    if ($action === 'edit' && $edit_id > 0) {
        $stmt = db()->prepare("SELECT id, name, ruc, contact_name, phone, email, address, notes FROM providers WHERE id = ? AND active = 1");
        $stmt->execute([$edit_id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($data) {
            $provider = $data;
        }
    }
    
    $title_form = ($action === 'new') ? 'Nuevo Proveedor' : 'Editar Proveedor';
    
    $content = '
    <div class="card">
        <div class="card-header"><h5 class="mb-0">' . $title_form . '</h5></div>
        <div class="card-body">
            <form method="POST" action="?page=providers&action=save">
                <input type="hidden" name="provider_id" value="' . intval($provider['id']) . '">
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Nombre *</label>
                        <input type="text" name="name" class="form-control" required value="' . htmlspecialchars($provider['name']) . '">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">RUC</label>
                        <input type="text" name="ruc" class="form-control" value="' . htmlspecialchars($provider['ruc'] ?? '') . '" placeholder="1976712-9">
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Contacto</label>
                        <input type="text" name="contact_name" class="form-control" value="' . htmlspecialchars($provider['contact_name']) . '">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Teléfono</label>
                        <input type="text" name="phone" class="form-control" value="' . htmlspecialchars($provider['phone']) . '">
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" value="' . htmlspecialchars($provider['email']) . '">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Dirección</label>
                        <input type="text" name="address" class="form-control" value="' . htmlspecialchars($provider['address']) . '">
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Notas</label>
                    <textarea name="notes" class="form-control" rows="3">' . htmlspecialchars($provider['notes']) . '</textarea>
                </div>
                
                <button type="submit" class="btn btn-primary">Guardar</button>
                <a href="?page=providers" class="btn btn-secondary">Cancelar</a>
            </form>
        </div>
    </div>';
    return;
}

// ===== LISTAR PROVEEDORES =====
$providers = db()->query("SELECT id, name, ruc, contact_name, phone, email, address FROM providers WHERE active = 1 ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);

$rows_html = '';
foreach ($providers as $p) {
    $ruc_display = (isset($p['ruc']) && !empty($p['ruc'])) ? htmlspecialchars($p['ruc']) : '-';
    $contact_display = (isset($p['contact_name']) && !empty($p['contact_name'])) ? htmlspecialchars($p['contact_name']) : '-';
    $phone_display = (isset($p['phone']) && !empty($p['phone'])) ? htmlspecialchars($p['phone']) : '-';
    $email_display = (isset($p['email']) && !empty($p['email'])) ? htmlspecialchars($p['email']) : '-';
    $address_display = (isset($p['address']) && !empty($p['address'])) ? htmlspecialchars($p['address']) : '-';
    
    $rows_html .= '<tr>
        <td>' . htmlspecialchars($p['name']) . '</td>
        <td>' . $ruc_display . '</td>
        <td>' . $contact_display . '</td>
        <td>' . $phone_display . '</td>
        <td>' . $email_display . '</td>
        <td>' . $address_display . '</td>
        <td>
            <a href="?page=providers&action=edit&id=' . $p['id'] . '" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
            <a href="?page=providers&action=delete&id=' . $p['id'] . '" class="btn btn-sm btn-outline-danger" onclick="return confirm(\'Confirmar?\')"><i class="bi bi-trash"></i></a>
        </td>
    </tr>';
}

$content = '
<div class="row mb-3">
    <div class="col-md-6">
        <a href="?page=providers&action=new" class="btn btn-primary"><i class="bi bi-plus-circle"></i> Nuevo Proveedor</a>
    </div>
    <div class="col-md-6">
        <input type="text" id="search" class="form-control" placeholder="Buscar...">
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <table class="table table-hover mb-0" id="tabla">
            <thead class="table-light">
                <tr>
                    <th>Nombre</th>
                    <th>RUC</th>
                    <th>Contacto</th>
                    <th>Teléfono</th>
                    <th>Email</th>
                    <th>Dirección</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                ' . $rows_html . '
            </tbody>
        </table>
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
