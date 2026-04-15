<!DOCTYPE html>
<html>
<head>
    <title>Backup - Ferretería Pro</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; }
        .backup-card { background: #f5f5f5; padding: 20px; border-radius: 8px; max-width: 500px; }
        .btn { background: #28a745; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; text-decoration: none; display: inline-block; }
        .btn:hover { background: #218838; }
        .info { margin-top: 20px; color: #666; font-size: 14px; }
        h2 { margin-top: 0; }
    </style>
</head>
<body>
    <h2>Respaldo de Base de Datos</h2>
    <div class="backup-card">
        <p>Generar un respaldo completo de la base de datos SQLite.</p>
        
        <?php if (isset($_GET['success'])): ?>
            <p style="color: green;"><?= htmlspecialchars($_GET['success']) ?></p>
        <?php endif; ?>
        
        <?php if (isset($_GET['error'])): ?>
            <p style="color: red;"><?= htmlspecialchars($_GET['error']) ?></p>
        <?php endif; ?>
        
        <form method="post" action="?page=backup_create">
            <button type="submit" class="btn">Descargar Backup</button>
        </form>
        
        <div class="info">
            <p><strong>Último backup:</strong> 
            <?php 
            $settings = db()->query("SELECT value FROM settings WHERE key = 'last_backup'")->fetchColumn();
            echo $settings ? $settings : 'Nunca';
            ?></p>
        </div>
    </div>
    
    <p><a href="?page=dashboard">← Volver al inicio</a></p>
</body>
</html>