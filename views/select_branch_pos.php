<?php
$title = 'Seleccionar Sucursal y Caja';
$pageTitle = 'Seleccionar Punto de Venta';

// Get all branches
$branches = db()->query("SELECT id, name, establishment_code, city FROM branches WHERE active = 1 ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-body p-5">
                    <div class="text-center mb-5">
                        <h1 class="card-title mb-2">Seleccionar Sucursal y Caja</h1>
                        <p class="text-muted">Selecciona en qué sucursal y caja trabajarás hoy</p>
                    </div>

                    <form id="branchPosForm" method="POST" action="?page=select_branch_pos">
                        <div class="form-group mb-4">
                            <label for="branch_id" class="form-label fw-bold">Sucursal</label>
                            <select class="form-select form-select-lg" id="branch_id" name="branch_id" required onchange="updatePOSTerminals()">
                                <option value="">-- Seleccionar sucursal --</option>
                                <?php foreach ($branches as $branch): ?>
                                    <option value="<?php echo $branch['id']; ?>">
                                        <?php echo htmlspecialchars($branch['name']); ?> (<?php echo htmlspecialchars($branch['establishment_code']); ?>) - <?php echo htmlspecialchars($branch['city']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group mb-4">
                            <label for="pos_terminal_id" class="form-label fw-bold">Caja / Punto de Venta</label>
                            <select class="form-select form-select-lg" id="pos_terminal_id" name="pos_terminal_id" required>
                                <option value="">-- Seleccionar caja --</option>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-primary btn-lg w-100">
                            <i class="fas fa-check-circle"></i> Comenzar Sesión
                        </button>
                    </form>

                    <hr class="my-4">
                    
                    <div class="alert alert-info" role="alert">
                        <i class="fas fa-info-circle"></i> 
                        <strong>Nota:</strong> Esta selección se guardará para esta computadora. Cada terminal debe seleccionar su sucursal y caja correspondiente.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .form-select-lg {
        padding: 0.75rem 1rem;
        font-size: 1.1rem;
    }

    .btn-lg {
        padding: 0.75rem 1.5rem;
        font-size: 1.1rem;
    }
</style>

<script>
function updatePOSTerminals() {
    const branchId = document.getElementById('branch_id').value;
    const posSelect = document.getElementById('pos_terminal_id');
    
    if (!branchId) {
        posSelect.innerHTML = '<option value="">-- Seleccionar caja --</option>';
        return;
    }

    // Fetch POS terminals for this branch
    fetch(`?page=api&action=get_pos_terminals&branch_id=${branchId}`)
        .then(response => response.json())
        .then(data => {
            posSelect.innerHTML = '<option value="">-- Seleccionar caja --</option>';
            data.forEach(pos => {
                const option = document.createElement('option');
                option.value = pos.id;
                option.textContent = `${pos.terminal_name} (${pos.pos_code})`;
                posSelect.appendChild(option);
            });
        })
        .catch(error => console.error('Error:', error));
}
</script>
