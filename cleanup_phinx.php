<?php
require __DIR__ . '/vendor/autoload.php';
$settings = require __DIR__ . '/app/config/settings.php';
$db = $settings['settings']['db'];

try {
    $pdo = new PDO("mysql:host={$db['host']};dbname={$db['database']}", $db['username'], $db['password']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // 1. Drop inconsistent empty tables
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
    
    $tablesToDrop = ['evidences', 'evaluations', 'attendances', 'sessions', 'students', 'course_groups', 'competencies'];
    foreach ($tablesToDrop as $table) {
        try {
            $pdo->exec("DROP TABLE IF EXISTS $table");
            echo "Drop suceso: $table\n";
        } catch (Exception $e) {
            echo "Error dropeando $table: " . $e->getMessage() . "\n";
        }
    }
    
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");

    // 2. Remove log entry for competencies so Phinx can run it again properly
    $pdo->exec("DELETE FROM phinxlog WHERE version = '20260313172760'");
    echo "Log de competencies limpiado.\n";

    // 3. Ensure columns additions are marked as done (since they are already there and we won't drop teachers/academic_years)
    $versionsToKeep = [
        ['version' => '20260314062106', 'migration_name' => 'AddGenderToTeachersTable'],
        ['version' => '20260314154147', 'migration_name' => 'AddIsCurrentToAcademicYears']
    ];
    $stmt = $pdo->prepare("INSERT IGNORE INTO phinxlog (version, migration_name, start_time, end_time, breakpoint) VALUES (?, ?, NOW(), NOW(), 0)");
    foreach ($versionsToKeep as $v) {
        $stmt->execute([$v['version'], $v['migration_name']]);
        echo "Marcado como hecho: {$v['version']}\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
