<?php
try {
    $pdo = new PDO('mysql:host=localhost;dbname=atalledo_v3_registroux_db', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "--- ESTRUCTURA DE LA TABLA attendances ---\n";
    $stmt = $pdo->query("DESCRIBE attendances");
    $cols = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($cols as $col) {
        echo "Columna: " . $col['Field'] . " | Tipo: " . $col['Type'] . "\n";
    }

} catch (PDOException $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
