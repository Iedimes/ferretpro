<?php
$user = user();
$content = null;
?>

<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-md-8">
            <!-- User Info Card -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Mi Perfil</h5>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <input type="hidden" name="action" value="update_info">
                        
                        <div class="mb-3">
                            <label class="form-label">Nombre</label>
                            <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($user['name'] ?? '') ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email'] ?? '') ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Rol</label>
                            <div class="form-control-plaintext">
                                <span class="badge bg-info"><?= ucfirst($user['role'] ?? 'N/A') ?></span>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Miembro desde</label>
                            <div class="form-control-plaintext">
                                <?php 
                                $createdDate = $user['created_at'] ?? null;
                                echo $createdDate ? date('d/m/Y H:i', strtotime($createdDate)) : 'N/A';
                                ?>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">Actualizar Información</button>
                    </form>
                </div>
            </div>
            
            <!-- Change Password Card -->
            <div class="card mb-4">
                <div class="card-header bg-warning text-white">
                    <h5 class="mb-0">Cambiar Contraseña</h5>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <input type="hidden" name="action" value="change_password">
                        
                        <div class="mb-3">
                            <label class="form-label">Contraseña Actual</label>
                            <input type="password" name="current_password" class="form-control" required>
                            <small class="text-muted">Por seguridad, debe confirmar su contraseña actual</small>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Nueva Contraseña</label>
                            <input type="password" name="new_password" class="form-control" required minlength="8">
                            <small class="text-muted">Mínimo 8 caracteres</small>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Confirmar Nueva Contraseña</label>
                            <input type="password" name="confirm_password" class="form-control" required minlength="8">
                        </div>
                        
                        <button type="submit" class="btn btn-warning">Cambiar Contraseña</button>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Login History Sidebar -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">Historial de Login (Últimas 10)</h5>
                </div>
                <div class="card-body" style="max-height: 600px; overflow-y: auto;">
                    <?php if (empty($loginHistory)): ?>
                        <p class="text-muted">Sin historial de login</p>
                    <?php else: ?>
                        <div class="list-group">
                            <?php foreach ($loginHistory as $entry): ?>
                                <div class="list-group-item list-group-item-action py-2" style="border: 0; padding: 10px 0;">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <small class="text-muted d-block">
                                                <?= date('d/m/Y H:i', strtotime($entry['created_at'])) ?>
                                            </small>
                                            <span class="badge <?= $entry['status'] === 'success' ? 'bg-success' : 'bg-danger' ?>">
                                                <?= ucfirst($entry['status']) ?>
                                            </span>
                                            <?php if ($entry['failed_reason']): ?>
                                                <small class="text-muted d-block mt-1">
                                                    <?= htmlspecialchars($entry['failed_reason']) ?>
                                                </small>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <small class="text-muted d-block mt-1">
                                        IP: <?= htmlspecialchars($entry['ip_address'] ?? 'N/A') ?>
                                    </small>
                                </div>
                                <hr class="my-2">
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
