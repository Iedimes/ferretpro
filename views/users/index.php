<?php 
$title = 'Usuarios';
$pageTitle = 'Gestión de Usuarios';

$users = db()->query("SELECT id, name, email, role, active, created_at FROM users ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);

$content = '<div class="row mb-3">
    <div class="col-md-6">
        <a href="?page=users&action=new" class="btn btn-primary">Nuevo Usuario</a>
    </div>
    <div class="col-md-6">
        <input type="text" id="searchUsers" class="form-control" placeholder="Buscar usuarios...">
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <table class="table table-hover mb-0" id="usersTable">
            <thead class="table-light">
                <tr>
                    <th>Nombre</th>
                    <th>Email</th>
                    <th>Rol</th>
                    <th>Estado</th>
                    <th>Creado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>';

if (empty($users)) {
    $content .= '<tr><td colspan="6" class="text-center text-muted">No hay usuarios</td></tr>';
} else {
    foreach ($users as $u) {
        $roleColor = match($u['role']) {
            'admin' => 'danger',
            'vendedor' => 'info',
            'gerente' => 'warning',
            default => 'secondary'
        };
        
        $statusColor = $u['active'] ? 'success' : 'danger';
        $statusText = $u['active'] ? 'Activo' : 'Inactivo';
        
        $content .= '<tr>
            <td>' . htmlspecialchars($u['name']) . '</td>
            <td>' . htmlspecialchars($u['email']) . '</td>
            <td><span class="badge bg-' . $roleColor . '">' . ucfirst($u['role']) . '</span></td>
            <td><span class="badge bg-' . $statusColor . '">' . $statusText . '</span></td>
            <td>' . date('d/m/Y', strtotime($u['created_at'])) . '</td>
            <td>
                <a href="?page=users&action=edit&id=' . $u['id'] . '" class="btn btn-sm btn-outline-primary">Editar</a>
                <a href="?page=users&action=toggle&id=' . $u['id'] . '" class="btn btn-sm btn-outline-warning">' . ($u['active'] ? 'Desactivar' : 'Activar') . '</a>
            </td>
        </tr>';
    }
}

$content .= '</tbody>
        </table>
    </div>
</div>

<script>
document.getElementById("searchUsers").addEventListener("input", function() {
    const term = this.value.toLowerCase();
    const rows = document.querySelectorAll("#usersTable tbody tr");
    rows.forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(term) ? "" : "none";
    });
});
</script>';

?>
