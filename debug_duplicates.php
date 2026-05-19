<?php
require __DIR__ . '/vendor/autoload.php';

$settings = (require __DIR__ . '/app/Config/settings.php')['settings']['db'];

use Illuminate\Database\Capsule\Manager as Capsule;

$capsule = new Capsule;
$capsule->addConnection($settings);
$capsule->setAsGlobal();
$capsule->bootEloquent();

$teacherEmail = 'margaritasmsm62@gmail.com';
$teacher = Capsule::table('teachers')
    ->join('users', 'teachers.user_id', '=', 'users.id')
    ->where('users.email', $teacherEmail)
    ->select('teachers.id')
    ->first();

if (!$teacher) {
    die("Teacher not found: $teacherEmail\n");
}

$teacherId = $teacher->id;
echo "Teacher ID: $teacherId ($teacherEmail)\n";

// Count students for this teacher
$count = Capsule::table('students')
    ->where('teacher_id', $teacherId)
    ->whereNull('deleted_at')
    ->count();

echo "Total enrollment records for this teacher: $count\n";

// Count unique students by DNI
$uniqueDniCount = Capsule::table('students')
    ->where('teacher_id', $teacherId)
    ->whereNull('deleted_at')
    ->where('dni', '!=', '')
    ->distinct('dni')
    ->count('dni');

echo "Unique students (with DNI) for this teacher: $uniqueDniCount\n";

// Find duplicates (same full_name, different record)
$duplicates = Capsule::table('students')
    ->where('teacher_id', $teacherId)
    ->whereNull('deleted_at')
    ->select('full_name')
    ->selectRaw('COUNT(*) as occurrences')
    ->groupBy('full_name')
    ->having('occurrences', '>', 1)
    ->get();

if ($duplicates->isEmpty()) {
    echo "No duplicate full_name records found for this teacher.\n";
} else {
    echo "Found " . $duplicates->count() . " students with multiple records for this teacher:\n";
    foreach ($duplicates as $d) {
        echo " - {$d->full_name}: {$d->occurrences} records\n";
    }
}

// Check for empty DNIs
$emptyDniCount = Capsule::table('students')
    ->where('teacher_id', $teacherId)
    ->whereNull('deleted_at')
    ->where('dni', '=', '')
    ->count();

echo "Records with empty DNI for this teacher: $emptyDniCount\n";
