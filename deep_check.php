<?php
require __DIR__ . '/vendor/autoload.php';
$settings = require __DIR__ . '/app/config/settings.php';
$db = $settings['settings']['db'];

try {
    $pdo = new PDO("mysql:host={$db['host']};dbname={$db['database']}", $db['username'], $db['password']);
    
    echo "--- COMPETENCIES CONTENT ---\n";
    $stmt = $pdo->query("SELECT * FROM competencies");
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));

    echo "\n--- CHECKING FOR HIDDEN TABLES ---\n";
    $stmt = $pdo->query("SELECT TABLE_NAME FROM information_schema.tables WHERE table_schema = '{$db['database']}'");
    print_r($stmt->fetchAll(PDO::FETCH_COLUMN));

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
