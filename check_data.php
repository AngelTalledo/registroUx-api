<?php
require __DIR__ . '/vendor/autoload.php';

// Assuming Eloquent is already connected if we boot the app or just set it up.
// For this environment, let's try to use the models directly if they are available.
// I'll check if I can just use SQLite.

$dbFile = __DIR__ . '/db/database.sqlite'; // typical path from list_dir before
if (!file_exists($dbFile)) {
    $dbFile = __DIR__ . '/database/database.sqlite';
}

echo "Checking DB: $dbFile\n";
if (file_exists($dbFile)) {
    $db = new PDO("sqlite:$dbFile");
    
    $tables = ['competencies', 'sessions_competencia', 'evaluations', 'students'];
    foreach ($tables as $table) {
        $count = $db->query("SELECT count(*) FROM $table")->fetchColumn();
        echo "Table $table: $count records\n";
    }

    // Check sessions_competencia content
    $rows = $db->query("SELECT * FROM sessions_competencia LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
    echo "\nsessions_competencia sample:\n";
    print_r($rows);
} else {
    echo "DB NOT FOUND\n";
}
