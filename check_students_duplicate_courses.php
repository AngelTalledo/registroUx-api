<?php
require __DIR__ . '/vendor/autoload.php';

$settings = (require __DIR__ . '/app/Config/settings.php')['settings']['db'];

use Illuminate\Database\Capsule\Manager as Capsule;

$capsule = new Capsule;
$capsule->addConnection($settings);
$capsule->setAsGlobal();
$capsule->bootEloquent();

try {
    echo "Analizando estudiantes con múltiples cursos para el mismo profesor...\n";
    echo "------------------------------------------------------------------\n";

    $results = Capsule::table('students')
        ->select('dni', 'full_name', 'teacher_id')
        ->selectRaw('COUNT(DISTINCT course_id) as course_count')
        ->where('status', true)
        ->whereNull('deleted_at')
        ->groupBy('dni', 'full_name', 'teacher_id')
        ->having('course_count', '>', 1)
        ->get();

    if ($results->isEmpty()) {
        echo "No se encontraron estudiantes en múltiples cursos con el mismo profesor.\n";
    } else {
        echo "Se encontraron " . $results->count() . " estudiantes en esta situación:\n\n";
        
        foreach ($results as $row) {
            echo "Estudiante: {$row->full_name} (DNI: {$row->dni})\n";
            echo "Profesor ID: {$row->teacher_id}\n";
            echo "Cantidad de Cursos: {$row->course_count}\n";
            
            // Buscar los cursos específicos
            $courses = Capsule::table('students')
                ->join('courses', 'students.course_id', '=', 'courses.id')
                ->join('grades', 'students.grade_id', '=', 'grades.id')
                ->join('classrooms', 'students.classroom_id', '=', 'classrooms.id')
                ->where('students.dni', $row->dni)
                ->where('students.teacher_id', $row->teacher_id)
                ->whereNull('students.deleted_at')
                ->select('courses.name as course_name', 'grades.name as grade_name', 'classrooms.section')
                ->get();
                
            foreach ($courses as $c) {
                echo "  - {$c->course_name} ({$c->grade_name} - {$c->section})\n";
            }
            echo "------------------------------------------------------------------\n";
        }
    }

} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
