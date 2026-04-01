<?php
require __DIR__ . '/vendor/autoload.php';

$settings = [
    'db' => [
        'driver' => 'mysql',
        'host' => 'localhost',
        'database' => 'registroux_db',
        'username' => 'root',
        'password' => '',
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
        'prefix' => '',
    ]
];

use Illuminate\Database\Capsule\Manager as Capsule;

$capsule = new Capsule;
$capsule->addConnection($settings['db']);
$capsule->setAsGlobal();
$capsule->bootEloquent();

try {
    $columns = Capsule::schema()->getColumnListing('academic_years');
    echo "Columnas en academic_years:\n";
    print_r($columns);
    
    // Check if teacher_id exists
    if (in_array('teacher_id', $columns)) {
        echo "\nOK: teacher_id existe.\n";
    } else {
        echo "\nERROR: teacher_id NO existe en la tabla academic_years.\n";
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
