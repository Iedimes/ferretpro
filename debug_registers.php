<?php
require 'class/Database.php';
$db = Database::getInstance();
$r = $db->query('SELECT * FROM cash_register ORDER BY id DESC LIMIT 3')->fetchAll(PDO::FETCH_ASSOC);
echo "Last 3 cash registers:\n";
foreach ($r as $row) {
    echo "ID: " . $row['id'] . " - Status: " . $row['status'] . " - Opened: " . $row['opened_at'] . "\n";
}
