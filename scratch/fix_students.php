<?php
require 'vendor/autoload.php';
$app = require_once 'public/index.php';

use App\Models\Student;

// Fix the 21 students: move them from Classroom 11 (Segundo E) to Classroom 6 (Primero E)
// only for those who are correctly marked as Grade 3 (Primero)
$affected = Student::where('teacher_id', 2)
    ->where('classroom_id', 11)
    ->where('grade_id', 3)
    ->update(['classroom_id' => 6]);

echo "Corrected {$affected} students.\n";
exit();
