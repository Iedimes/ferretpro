<?php 
$title = 'Categorías';
$pageTitle = 'Gestión de Categorías';

$action = $_GET['action'] ?? 'list';
$category_id = $_GET['id'] ?? null;

if ($action === 'save' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $description = $_POST['description'] ?? '';
    $parent_id = $_POST['parent_id'] ?: null;
    $category_id_form = intval($_POST['category_id'] ?? 0);
    
    try {
        if ($category_id_form > 0) {
            $stmt = db()->prepare("UPDATE categories SET name=?, description=?, parent_id=? WHERE id=?");
            $stmt->execute([$name, $description, $parent_id, $category_id_form]);
            flash('success', 'Categoría actualizada correctamente');
        } else {
            $stmt = db()->prepare("INSERT INTO categories (name, description, parent_id) VALUES (?, ?, ?)");
            $stmt->execute([$name, $description, $parent_id]);
            flash('success', 'Categoría creada correctamente');
        }
    } catch (Exception $e) {
        flash('error', 'Error: ' . $e->getMessage());
    }
    redirect('?page=categories');
}

if ($action === 'delete' && $category_id) {
    $stmt = db()->prepare("UPDATE categories SET active = 0 WHERE id = ?");
    $stmt->execute([$category_id]);
    flash('success', 'Categoría eliminada');
    redirect('?page=categories');
}

if ($action === 'new' || $action === 'edit') {
    $category = ['id' => '', 'name' => '', 'description' => '', 'parent_id' => ''];
    if ($action === 'edit' && $category_id) {
        $stmt = db()->prepare("SELECT * FROM categories WHERE id = ?");
        $stmt->execute([$category_id]);
        $category = array_merge($category, $stmt->fetch(PDO::FETCH_ASSOC));
    }
    $categories = db()->query("SELECT * FROM categories WHERE active = 1 AND id != $category_id ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
    
    $content = '
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">' . ($action === 'new' ? 'Nueva Categoría' : 'Editar Categoría') . '</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="?page=categories&action=save">
                <input type="hidden" name="category_id" value="' . intval($category['id']) . '">
                <div class="mb-3">
                    <label class="form-label">Nombre *</label>
                    <input type="text" name="name" class="form-control" required value="' . htmlspecialchars($category['name']) . '">
                </div>
                <div class="mb-3">
                    <label class="form-label">Descripción</label>
                    <textarea name="description" class="form-control" rows="2">' . htmlspecialchars($category['description']) . '</textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Categoría Padre</label>
                    <select name="parent_id" class="form-select">
                        <option value="">Ninguna</option>
                        ' . implode('', array_map(fn($c) => "<option value=\"{$c['id']}\" " . ($c['id'] == $category['parent_id'] ? 'selected' : '') . ">{$c['name']}</option>", $categories)) . '
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Guardar</button>
                <a href="?page=categories" class="btn btn-secondary">Cancelar</a>
            </form>
        </div>
    </div>';
    
    return;
}

$categories = db()->query("SELECT c.*, (SELECT COUNT(*) FROM products WHERE category_id = c.id) as product_count FROM categories c WHERE c.active = 1 ORDER BY c.name")->fetchAll(PDO::FETCH_ASSOC);

$content = '
<div class="row mb-3">
    <div class="col-md-6">
        <a href="?page=categories&action=new" class="btn btn-primary"><i class="bi bi-tag"></i> Nueva Categoría</a>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Nombre</th>
                    <th>Descripción</th>
                    <th>Productos</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>';
foreach ($categories as $c) {
    $content .= '<tr>
        <td>' . htmlspecialchars($c['name']) . '</td>
        <td>' . htmlspecialchars($c['description']) . '</td>
        <td>' . $c['product_count'] . '</td>
        <td>
            <a href="?page=categories&action=edit&id=' . $c['id'] . '" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
            <a href="?page=categories&action=delete&id=' . $c['id'] . '" class="btn btn-sm btn-outline-danger" onclick="return confirm(\'¿Eliminar?\')"><i class="bi bi-trash"></i></a>
        </td>
    </tr>';
}
$content .= '</tbody>
        </table>
    </div>
</div>';


