<?php 
$title = 'Puntos de Venta';
$pageTitle = 'Gestión de Puntos de Venta / Cajas';

$branches = db()->query("SELECT * FROM branches WHERE active = 1 ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
$branch_id = $_GET['branch'] ?? ($branches[0]['id'] ?? null);

$posTerminals = [];
if ($branch_id) {
    $posTerminals = db()->query("SELECT * FROM pos_terminals WHERE branch_id = $branch_id ORDER BY pos_code")->fetchAll(PDO::FETCH_ASSOC);
}

$content = '
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4><i class="bi bi-cash-register"></i> Puntos de Venta / Cajas</h4>
    <button type="button" class="btn btn-primary" onclick="showPosModal()"><i class="bi bi-plus"></i> Nueva Caja</button>
</div>

<div class="mb-3">
    <label class="form-label">Seleccionar Sucursal:</label>
    <select id="branchSelect" class="form-select" onchange="changeBranch()">
        <option value="">-- Seleccionar --</option>';

foreach ($branches as $b) {
    $selected = $b['id'] == $branch_id ? 'selected' : '';
    $content .= '<option value="' . $b['id'] . '" ' . $selected . '>' . htmlspecialchars($b['name']) . ' (' . $b['establishment_code'] . ')</option>';
}

$content .= '</select>
</div>';

if ($branch_id) {
    $content .= '
<div class="card">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Código</th>
                    <th>Nombre</th>
                    <th>Estado</th>
                    <th>Creada</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>';

    foreach ($posTerminals as $pos) {
        $status = $pos['is_active'] ? '<span class="badge bg-success">Activa</span>' : '<span class="badge bg-danger">Inactiva</span>';
        $content .= '<tr>
            <td><strong>' . htmlspecialchars($pos['pos_code']) . '</strong></td>
            <td>' . htmlspecialchars($pos['terminal_name'] ?? 'Caja ' . $pos['pos_code']) . '</td>
            <td>' . $status . '</td>
            <td>' . Format::date($pos['created_at']) . '</td>
            <td>
                <button class="btn btn-sm btn-outline-primary" onclick="editPos(' . $pos['id'] . ')"><i class="bi bi-pencil"></i></button>
                <button class="btn btn-sm btn-outline-danger" onclick="deletePos(' . $pos['id'] . ')"><i class="bi bi-trash"></i></button>
            </td>
        </tr>';
    }

    $content .= '</tbody>
        </table>
    </div>
</div>';
}

$content .= '
<!-- Modal para crear/editar POS -->
<div class="modal fade" id="posModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="posModalTitle"><i class="bi bi-cash-register"></i> Nueva Caja</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" id="posForm" onsubmit="savePos(event)">
                <input type="hidden" name="pos_id" id="posId">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Sucursal *</label>
                        <select name="branch_id" id="posBranchId" class="form-select" required>
                            <option value="">-- Seleccionar --</option>';

foreach ($branches as $b) {
    $selected = $b['id'] == $branch_id ? 'selected' : '';
    $content .= '<option value="' . $b['id'] . '" ' . $selected . '>' . htmlspecialchars($b['name']) . '</option>';
}

$content .= '</select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Código de Caja *</label>
                        <input type="text" name="pos_code" id="posCode" class="form-control" placeholder="001" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nombre de Terminal</label>
                        <input type="text" name="terminal_name" id="posName" class="form-control" placeholder="Caja 1">
                    </div>
                    <div class="mb-3">
                        <label class="form-check-label">
                            <input type="checkbox" name="is_active" id="posActive" class="form-check-input" checked>
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
function changeBranch() {
    const branch_id = document.getElementById("branchSelect").value;
    if (branch_id) {
        window.location.href = "?page=pos_terminals&branch=" + branch_id;
    }
}

function showPosModal() {
    const branch_id = document.getElementById("branchSelect").value;
    if (!branch_id) {
        alert("Por favor selecciona una sucursal");
        return;
    }
    document.getElementById("posId").value = "";
    document.getElementById("posForm").reset();
    document.getElementById("posBranchId").value = branch_id;
    document.getElementById("posModalTitle").textContent = "Nueva Caja";
    new bootstrap.Modal(document.getElementById("posModal")).show();
}

function editPos(id) {
    fetch("?page=pos_terminals&action=get&id=" + id)
        .then(r => r.json())
        .then(data => {
            document.getElementById("posId").value = data.id;
            document.getElementById("posBranchId").value = data.branch_id;
            document.getElementById("posCode").value = data.pos_code;
            document.getElementById("posName").value = data.terminal_name || "";
            document.getElementById("posActive").checked = data.is_active == 1;
            document.getElementById("posModalTitle").textContent = "Editar Caja";
            new bootstrap.Modal(document.getElementById("posModal")).show();
        });
}

function savePos(e) {
    e.preventDefault();
    const formData = new FormData(document.getElementById("posForm"));
    fetch("?page=pos_terminals&action=save", {method: "POST", body: formData})
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message || "Error al guardar");
            }
        });
}

function deletePos(id) {
    if (confirm("¿Eliminar esta caja?")) {
        fetch("?page=pos_terminals&action=delete&id=" + id, {method: "POST"})
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
