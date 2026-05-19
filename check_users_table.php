<?php
require __DIR__ . '/vendor/autoload.php';

$settings = (require __DIR__ . '/app/Config/settings.php')['settings']['db'];

use Illuminate\Database\Capsule\Manager as Capsule;

$capsule = new Capsule;
$capsule->addConnection($settings);
$capsule->setAsGlobal();
$capsule->bootEloquent();

try {
    echo "Checking connection to database: " . $settings['database'] . "\n";
    
    if (!Capsule::schema()->hasTable('users')) {
        echo "ERROR: Table 'users' does not exist!\n";
        
        $tables = Capsule::select('SHOW TABLES');
        echo "Existing tables:\n";
        foreach ($tables as $table) {
            echo "- " . current((array)$table) . "\n";
        }
    } else {
        echo "SUCCESS: Table 'users' exists.\n";
        $columns = Capsule::schema()->getColumnListing('users');
        echo "Columns in 'users':\n";
        print_r($columns);
        
        $count = Capsule::table('users')->count();
        echo "Total users: $count\n";
        
        if ($count > 0) {
            $user = Capsule::table('users')->first();
            echo "First user email: " . $user->email . "\n";
        }
    }
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
