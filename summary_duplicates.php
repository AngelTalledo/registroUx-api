<?php
require __DIR__ . '/vendor/autoload.php';

$settings = (require __DIR__ . '/app/Config/settings.php')['settings']['db'];

use Illuminate\Database\Capsule\Manager as Capsule;

$capsule = new Capsule;
$capsule->addConnection($settings);
$capsule->setAsGlobal();
$capsule->bootEloquent();

try {
    echo "Resumen de estudiantes en múltiples cursos/aulas con el mismo profesor:\n";
    echo "======================================================================\n";

    $results = Capsule::table('students')
        ->select('full_name', 'teacher_id')
        ->selectRaw('COUNT(*) as total_records')
        ->whereNull('deleted_at')
        ->groupBy('full_name', 'teacher_id')
        ->having('total_records', '>', 1)
        ->get();

    if ($results->isEmpty()) {
        echo "No se encontraron estudiantes con múltiples registros para el mismo profesor.\n";
    } else {
        foreach ($results as $row) {
            $teacher = Capsule::table('users')
                ->join('teachers', 'users.id', '=', 'teachers.user_id')
                ->where('teachers.id', $row->teacher_id)
                ->value('email');

            echo "Estudiante: {$row->full_name}\n";
            echo "Profesor: {$teacher} (ID: {$row->teacher_id})\n";
            echo "Registros: {$row->total_records}\n";
            
            $enrollments = Capsule::table('students')
                ->join('courses', 'students.course_id', '=', 'courses.id')
                ->join('classrooms', 'students.classroom_id', '=', 'classrooms.id')
                ->where('students.full_name', $row->full_name)
                ->where('students.teacher_id', $row->teacher_id)
                ->whereNull('students.deleted_at')
                ->select('courses.name as course_name', 'classrooms.section')
                ->get();
                
            foreach ($enrollments as $e) {
                echo "  - {$e->course_name} (Sección: {$e->section})\n";
            }
            echo "----------------------------------------------------------------------\n";
        }
    }

} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
