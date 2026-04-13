<?php 
$title = 'Usuarios';
$pageTitle = $action === 'new' ? 'Nuevo Usuario' : 'Editar Usuario';

$user_id = $_GET['id'] ?? null;
$user = [
    'id' => '',
    'name' => '',
    'email' => '',
    'password' => '',
    'role' => 'vendedor',
    'active' => 1
];

if ($action === 'edit' && $user_id) {
    $stmt = db()->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = array_merge($user, $stmt->fetch(PDO::FETCH_ASSOC) ?? []);
}

if ($action === 'save' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? 'vendedor';
    
    if (empty($name) || empty($email)) {
        $error = 'Nombre y Email son requeridos';
    } else {
        if ($action === 'new') {
            if (empty($password)) {
                $error = 'Contraseña es requerida para nuevos usuarios';
            } else {
                $stmt = db()->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
                $stmt->execute([$name, $email, password_hash($password, PASSWORD_DEFAULT), $role]);
                header('Location: ?page=users');
                exit;
            }
        } else {
            if ($password) {
                $stmt = db()->prepare("UPDATE users SET name = ?, email = ?, password = ?, role = ? WHERE id = ?");
                $stmt->execute([$name, $email, password_hash($password, PASSWORD_DEFAULT), $role, $user_id]);
            } else {
                $stmt = db()->prepare("UPDATE users SET name = ?, email = ?, role = ? WHERE id = ?");
                $stmt->execute([$name, $email, $role, $user_id]);
            }
            header('Location: ?page=users');
            exit;
        }
    }
}

$content = '<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">' . ($action === 'new' ? 'Crear Nuevo Usuario' : 'Editar Usuario') . '</h5>
            </div>
            <div class="card-body">
                ' . (isset($error) ? '<div class="alert alert-danger">' . htmlspecialchars($error) . '</div>' : '') . '
                <form method="POST" action="?page=users&action=save">
                    <input type="hidden" name="id" value="' . $user['id'] . '">
                    
                    <div class="mb-3">
                        <label class="form-label">Nombre Completo *</label>
                        <input type="text" name="name" class="form-control" required value="' . htmlspecialchars($user['name'] ?? '') . '">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Email *</label>
                        <input type="email" name="email" class="form-control" required value="' . htmlspecialchars($user['email'] ?? '') . '">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Contraseña ' . ($action === 'edit' ? '(dejar en blanco para mantener actual)' : '*') . '</label>
                        <input type="password" name="password" class="form-control" ' . ($action === 'new' ? 'required' : '') . '>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Rol *</label>
                        <select name="role" class="form-select" required>
                            <option value="vendedor" ' . ($user['role'] === 'vendedor' ? 'selected' : '') . '>Vendedor</option>
                            <option value="gerente" ' . ($user['role'] === 'gerente' ? 'selected' : '') . '>Gerente</option>
                            <option value="admin" ' . ($user['role'] === 'admin' ? 'selected' : '') . '>Administrador</option>
                        </select>
                        <small class="text-muted d-block mt-2">
                            <strong>Vendedor:</strong> Acceso a POS y ventas<br>
                            <strong>Gerente:</strong> Acceso a reportes y configuración<br>
                            <strong>Administrador:</strong> Acceso completo
                        </small>
                    </div>
                    
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">Guardar Usuario</button>
                        <a href="?page=users" class="btn btn-secondary">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card bg-light">
            <div class="card-header">
                <h5 class="mb-0">Información</h5>
            </div>
            <div class="card-body">
                <p><strong>Roles Disponibles:</strong></p>
                <ul class="small">
                    <li><strong>Vendedor:</strong> Realiza ventas, ve su historial</li>
                    <li><strong>Gerente:</strong> Acceso a reportes completos</li>
                    <li><strong>Admin:</strong> Control total del sistema</li>
                </ul>
                
                ' . ($action === 'edit' ? '<hr><p class="small text-muted"><strong>Usuario creado:</strong> ' . date('d/m/Y H:i', strtotime($user['created_at'] ?? '')) . '</p>' : '') . '
            </div>
        </div>
    </div>
</div>';

?>
