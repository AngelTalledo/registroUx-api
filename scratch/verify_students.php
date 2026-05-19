<?php
require 'vendor/autoload.php';
$app = require_once 'public/index.php';

use App\Models\Grade;

$grades = Grade::where('teacher_id', 2)->get();
foreach ($grades as $g) {
    echo "ID: {$g->id}, Name: {$g->name}\n";
}
exit();
