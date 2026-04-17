<?php
require 'class/Database.php';
$db = Database::getInstance();
$db->exec("ALTER TABLE products ADD COLUMN image TEXT");
echo "Columna image agregada!";