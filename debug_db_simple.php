<?php
$pdo = new PDO('mysql:host=localhost;dbname=registroux_db', 'root', '');

echo "--- COLUMNS ---\n";
foreach($pdo->query("DESCRIBE diagnostic_evaluations") as $row) {
    echo $row['Field'] . " (" . $row['Type'] . ")\n";
}

echo "\n--- INDEXES ---\n";
foreach($pdo->query("SHOW INDEX FROM diagnostic_evaluations") as $row) {
    echo $row['Key_name'] . " -> " . $row['Column_name'] . "\n";
}
