<?php
require __DIR__ . '/vendor/autoload.php';
$settings = require __DIR__ . '/app/config/settings.php';
$db = $settings['settings']['db'];

try {
    $pdo = new PDO("mysql:host={$db['host']};dbname={$db['database']}", $db['username'], $db['password']);
    
    $versionsToAdd = [
        ['version' => '20260313172760', 'migration_name' => 'CreateCompetenciesTable'],
        ['version' => '20260314062106', 'migration_name' => 'AddGenderToTeachersTable'],
        ['version' => '20260314154147', 'migration_name' => 'AddIsCurrentToAcademicYears']
    ];

    $stmt = $pdo->prepare("INSERT IGNORE INTO phinxlog (version, migration_name, start_time, end_time, breakpoint) VALUES (?, ?, NOW(), NOW(), 0)");
    
    foreach ($versionsToAdd as $v) {
        $stmt->execute([$v['version'], $v['migration_name']]);
        echo "Agregado a phinxlog: {$v['version']} ({$v['migration_name']})\n";
    }

    echo "\nSincronización completada.\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
