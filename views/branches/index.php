<?php 
$title = 'Sucursales';
$pageTitle = 'Gestión de Sucursales';

$branches = db()->query("SELECT * FROM branches ORDER BY establishment_code")->fetchAll(PDO::FETCH_ASSOC);

$content = '
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4><i class="bi bi-building"></i> Sucursales</h4>
    <button type="button" class="btn btn-primary" onclick="showBranchModal()"><i class="bi bi-plus"></i> Nueva Sucursal</button>
</div>

<div class="card">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Código</th>
                    <th>Nombre</th>
                    <th>Ciudad</th>
                    <th>Teléfono</th>
                    <th>Email</th>
                    <th>Estado</th>
                    <th>Cajas</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>';

foreach ($branches as $b) {
    $status = $b['active'] ? '<span class="badge bg-success">Activa</span>' : '<span class="badge bg-danger">Inactiva</span>';
    $posCount = db()->query("SELECT COUNT(*) as count FROM pos_terminals WHERE branch_id = " . $b['id'])->fetch(PDO::FETCH_ASSOC)['count'];
    
    $content .= '<tr>
        <td><strong>' . htmlspecialchars($b['establishment_code']) . '</strong></td>
        <td>' . htmlspecialchars($b['name']) . '</td>
        <td>' . htmlspecialchars($b['city'] ?? '-') . '</td>
        <td>' . htmlspecialchars($b['phone'] ?? '-') . '</td>
        <td>' . htmlspecialchars($b['email'] ?? '-') . '</td>
        <td>' . $status . '</td>
        <td><span class="badge bg-info">' . $posCount . '</span></td>
        <td>
            <button class="btn btn-sm btn-outline-primary" onclick="editBranch(' . $b['id'] . ')"><i class="bi bi-pencil"></i></button>
            <button class="btn btn-sm btn-outline-danger" onclick="deleteBranch(' . $b['id'] . ')"><i class="bi bi-trash"></i></button>
        </td>
    </tr>';
}

$content .= '</tbody>
        </table>
    </div>
</div>

<!-- Modal para crear/editar sucursal -->
<div class="modal fade" id="branchModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="branchModalTitle"><i class="bi bi-building"></i> Nueva Sucursal</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" id="branchForm" onsubmit="saveBranch(event)">
                <input type="hidden" name="branch_id" id="branchId">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Código Establecimiento *</label>
                        <input type="text" name="establishment_code" id="establishmentCode" class="form-control" placeholder="001" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nombre de Sucursal *</label>
                        <input type="text" name="name" id="branchName" class="form-control" placeholder="Casa Matriz" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Ciudad</label>
                        <input type="text" name="city" id="branchCity" class="form-control" placeholder="Asunción">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Dirección</label>
                        <input type="text" name="address" id="branchAddress" class="form-control" placeholder="Calle Principal 123">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Teléfono</label>
                        <input type="text" name="phone" id="branchPhone" class="form-control" placeholder="+595 21 123456">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" id="branchEmail" class="form-control" placeholder="sucursal@empresa.com">
                    </div>
                    <div class="mb-3">
                        <label class="form-check-label">
                            <input type="checkbox" name="active" id="branchActive" class="form-check-input" checked>
                            Activa
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check"></i> Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function showBranchModal() {
    document.getElementById("branchId").value = "";
    document.getElementById("branchForm").reset();
    document.getElementById("branchModalTitle").textContent = "Nueva Sucursal";
    new bootstrap.Modal(document.getElementById("branchModal")).show();
}

function editBranch(id) {
    fetch("?page=branches&action=get&id=" + id)
        .then(r => r.json())
        .then(data => {
            document.getElementById("branchId").value = data.id;
            document.getElementById("establishmentCode").value = data.establishment_code;
            document.getElementById("branchName").value = data.name;
            document.getElementById("branchCity").value = data.city || "";
            document.getElementById("branchAddress").value = data.address || "";
            document.getElementById("branchPhone").value = data.phone || "";
            document.getElementById("branchEmail").value = data.email || "";
            document.getElementById("branchActive").checked = data.active == 1;
            document.getElementById("branchModalTitle").textContent = "Editar Sucursal";
            new bootstrap.Modal(document.getElementById("branchModal")).show();
        });
}

function saveBranch(e) {
    e.preventDefault();
    const formData = new FormData(document.getElementById("branchForm"));
    fetch("?page=branches&action=save", {method: "POST", body: formData})
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message || "Error al guardar");
            }
        });
}

function deleteBranch(id) {
    if (confirm("¿Eliminar esta sucursal?")) {
        fetch("?page=branches&action=delete&id=" + id, {method: "POST"})
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message || "Error al eliminar");
                }
            });
    }
}
</script>
';
?>
