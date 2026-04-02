<?php

require 'vendor/autoload.php';

$settings = (require 'app/config/settings.php')['settings']['db'];

try {
    $pdo = new PDO(
        "mysql:host={$settings['host']};dbname={$settings['database']};charset=utf8mb4",
        $settings['username'],
        $settings['password'],
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );

    echo "Conexión establecida con la base de datos: {$settings['database']}\n";

    $tables = [
        'evidences',
        'evaluations',
        'attendances',
        'sessions',
        'students',
        'course_groups',
        'competencies',
        'session_competencies', // Relacionada con evaluaciones/sesiones
        'evaluation_audit'      // Relacionada con evaluaciones
    ];

    $pdo->exec('SET FOREIGN_KEY_CHECKS = 0');

    foreach ($tables as $table) {
        // Verificar si la tabla existe antes de intentar truncar
        $stmt = $pdo->prepare("SHOW TABLES LIKE ?");
        $stmt->execute([$table]);
        if ($stmt->fetch()) {
            $pdo->exec("TRUNCATE TABLE `$table` ");
            echo "[OK] Tabla '$table' truncada correctamente.\n";
        } else {
            echo "[SKIP] La tabla '$table' no existe.\n";
        }
    }

    $pdo->exec('SET FOREIGN_KEY_CHECKS = 1');

    echo "\nLimpieza completada con éxito.\n";

} catch (PDOException $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
