<?php
require __DIR__ . '/vendor/autoload.php';

$settings = (require __DIR__ . '/app/Config/settings.php')['settings']['db'];

use Illuminate\Database\Capsule\Manager as Capsule;

$capsule = new Capsule;
$capsule->addConnection($settings);
$capsule->setAsGlobal();
$capsule->bootEloquent();

$fullName = 'PAZ MENDOZA JARUMI TATIANA';
$teacherId = 2;

$enrollments = Capsule::table('students')
    ->join('courses', 'students.course_id', '=', 'courses.id')
    ->join('grades', 'students.grade_id', '=', 'grades.id')
    ->join('classrooms', 'students.classroom_id', '=', 'classrooms.id')
    ->where('students.full_name', $fullName)
    ->where('students.teacher_id', $teacherId)
    ->whereNull('students.deleted_at')
    ->select('courses.name as course_name', 'grades.name as grade_name', 'classrooms.section', 'students.created_at')
    ->get();

echo "Detalles para $fullName con Profesor ID $teacherId:\n";
foreach ($enrollments as $e) {
    echo "- Curso: {$e->course_name} | Grado: {$e->grade_name} | Sección: {$e->section} | Creado: {$e->created_at}\n";
}
