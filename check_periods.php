<?php
require __DIR__ . '/vendor/autoload.php';
$settings = require __DIR__ . '/app/config/settings.php';
$dbSettings = $settings['settings']['db'];

use Illuminate\Database\Capsule\Manager as Capsule;

$capsule = new Capsule;
$capsule->addConnection($dbSettings);
$capsule->setAsGlobal();
$capsule->bootEloquent();

if (Capsule::schema()->hasTable('periods')) {
    echo "Table 'periods' exists.\n";
    $columns = Capsule::schema()->getColumnListing('periods');
    echo "Columns in 'periods':\n";
    print_r($columns);
} else {
    echo "Table 'periods' does NOT exist.\n";
}
