<?php
require_once __DIR__ . '/class/Database.php';
require_once __DIR__ . '/actions/helpers.php';

echo "=== Debug ===\n";
echo "GET params: ";
print_r($_GET);

$accounts = db()->query("SELECT ap.*, p.name as provider_name FROM accounts_payable ap JOIN providers p ON ap.provider_id = p.id WHERE ap.status != 'cancelada' ORDER BY ap.due_date ASC")->fetchAll(PDO::FETCH_ASSOC);
echo "Total before filter: " . count($accounts) . "\n";

$filter = $_GET['filter'] ?? null;
echo "Filter: " . $filter . "\n";

if ($filter === '10days' && !empty($accounts)) {
    $today = new DateTime();
    echo "Today: " . $today->format('Y-m-d') . "\n";
    $accounts = array_filter($accounts, function($a) use ($today) {
        $dueDate = new DateTime($a['due_date']);
        $days = $today->diff($dueDate)->days;
        echo "  - Account #{$a['id']}: due={$a['due_date']}, days={$days}\n";
        return $days >= 0 && $days <= 10;
    });
    echo "Total after filter: " . count($accounts) . "\n";
}