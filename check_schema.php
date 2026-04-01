<?php
require __DIR__ . '/vendor/autoload.php';
$settings = require __DIR__ . '/app/config/settings.php';
$db = $settings['settings']['db'];

try {
    $pdo = new PDO("mysql:host={$db['host']};dbname={$db['database']}", $db['username'], $db['password']);
    
    $tables = ['users', 'teachers', 'academic_years', 'periods', 'phinxlog'];
    foreach ($tables as $table) {
        echo "\n--- CREATE TABLE $table ---\n";
        try {
            $stmt = $pdo->query("SHOW CREATE TABLE $table");
            print_r($stmt->fetch(PDO::FETCH_ASSOC));
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage() . "\n";
        }
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
