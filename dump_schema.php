<?php
$pdo = new PDO('mysql:host=localhost;dbname=registroux_db', 'root', '');

echo "--- TABLES ---\n";
foreach($pdo->query("SHOW TABLES") as $row) {
    $table = $row[0];
    echo "Table: $table\n";
    foreach($pdo->query("SHOW INDEX FROM $table") as $index) {
        echo "  Index: " . $index['Key_name'] . " -> " . $index['Column_name'] . " (Unique: " . ($index['Non_unique'] ? 'No' : 'Yes') . ")\n";
    }
}

echo "\n--- FOREIGN KEYS (diagnostic_evaluations) ---\n";
$res = $pdo->query("
    SELECT 
        CONSTRAINT_NAME, 
        COLUMN_NAME, 
        REFERENCED_TABLE_NAME, 
        REFERENCED_COLUMN_NAME 
    FROM 
        information_schema.KEY_COLUMN_USAGE 
    WHERE 
        TABLE_SCHEMA = 'registroux_db' AND 
        TABLE_NAME = 'diagnostic_evaluations' AND 
        REFERENCED_TABLE_NAME IS NOT NULL
");
foreach($res as $row) {
    echo $row['CONSTRAINT_NAME'] . " (" . $row['COLUMN_NAME'] . ") references " . $row['REFERENCED_TABLE_NAME'] . "(" . $row['REFERENCED_COLUMN_NAME'] . ")\n";
}
