<?php

require 'vendor/autoload.php';

$settings = (require 'app/config/settings.php')['settings']['db'];

try {
    $pdo = new PDO(
        "mysql:host={$settings['host']};dbname={$settings['database']};charset=utf8mb4",
        $settings['username'],
        $settings['password']
    );

    $stmt = $pdo->query('SELECT * FROM diagnostic_evaluations');
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    print_r($results);

} catch (PDOException $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
