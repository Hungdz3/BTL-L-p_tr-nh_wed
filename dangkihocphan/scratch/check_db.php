<?php
require_once __DIR__ . '/../config/db.php';
$db = getDB();

// Ensure hoc_ky_mo column exists in mon_hoc table
try {
    $db->exec("ALTER TABLE mon_hoc ADD COLUMN IF NOT EXISTS hoc_ky_mo VARCHAR(20) DEFAULT 'CA_HAI'");
    echo "✅ Checked column hoc_ky_mo in mon_hoc table.\n";
} catch (Exception $e) {
    echo "Notice: " . $e->getMessage() . "\n";
}

$cols = $db->query("SELECT column_name FROM information_schema.columns WHERE table_name='mon_hoc'")->fetchAll(PDO::FETCH_COLUMN);
print_r($cols);
