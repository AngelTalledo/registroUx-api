<?php
require __DIR__ . '/vendor/autoload.php';
$settings = require __DIR__ . '/app/config/settings.php';
$db = $settings['settings']['db'];

try {
    $pdo = new PDO("mysql:host={$db['host']};dbname={$db['database']}", $db['username'], $db['password']);
    
    echo "--- PHINX LOG ---\n";
    $stmt = $pdo->query("SELECT * FROM phinxlog");
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));

    echo "\n--- TABLES IN DB ---\n";
    $stmt = $pdo->query("SHOW TABLES");
    $tablesInDb = $stmt->fetchAll(PDO::FETCH_COLUMN);
    print_r($tablesInDb);

    $expectedTables = [
        'users', 'teachers', 'academic_years', 'periods', 
        'competencies', 'course_groups', 'students', 
        'sessions', 'attendances', 'evaluations', 'evidences'
    ];

    echo "\n--- TABLE STATUS ---\n";
    foreach ($expectedTables as $table) {
        $exists = in_array($table, $tablesInDb) ? "EXISTE" : "FALTA";
        echo "$table: $exists\n";
    }

    if (in_array('teachers', $tablesInDb)) {
        echo "\n--- TEACHERS COLUMNS ---\n";
        $stmt = $pdo->query("DESCRIBE teachers");
        print_r($stmt->fetchAll(PDO::FETCH_COLUMN));
    }

    if (in_array('academic_years', $tablesInDb)) {
        echo "\n--- ACADEMIC_YEARS COLUMNS ---\n";
        $stmt = $pdo->query("DESCRIBE academic_years");
        print_r($stmt->fetchAll(PDO::FETCH_COLUMN));
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
