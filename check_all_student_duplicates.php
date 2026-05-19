<?php
require __DIR__ . '/vendor/autoload.php';

$settings = (require __DIR__ . '/app/Config/settings.php')['settings']['db'];

use Illuminate\Database\Capsule\Manager as Capsule;

$capsule = new Capsule;
$capsule->addConnection($settings);
$capsule->setAsGlobal();
$capsule->bootEloquent();

try {
    echo "Analizando estudiantes duplicados en el sistema...\n";
    echo "------------------------------------------------------------------\n";

    // Todos los estudiantes con el mismo DNI en diferentes cursos
    $results = Capsule::table('students')
        ->select('dni', 'full_name')
        ->selectRaw('COUNT(id) as total_enrollments')
        ->selectRaw('COUNT(DISTINCT teacher_id) as teacher_count')
        ->selectRaw('COUNT(DISTINCT course_id) as course_count')
        ->whereNull('deleted_at')
        ->groupBy('dni', 'full_name')
        ->having('total_enrollments', '>', 1)
        ->get();

    if ($results->isEmpty()) {
        echo "No se encontraron estudiantes duplicados en el sistema.\n";
    } else {
        echo "Se encontraron " . $results->count() . " estudiantes con múltiples registros:\n\n";
        
        foreach ($results as $row) {
            echo "Estudiante: {$row->full_name} (DNI: {$row->dni})\n";
            echo "Total registros: {$row->total_enrollments}\n";
            echo "Profesores distintos: {$row->teacher_count}\n";
            echo "Cursos distintos: {$row->course_count}\n";
            
            $enrollments = Capsule::table('students')
                ->join('courses', 'students.course_id', '=', 'courses.id')
                ->join('teachers', 'students.teacher_id', '=', 'teachers.id')
                ->join('users', 'teachers.user_id', '=', 'users.id')
                ->where('students.dni', $row->dni)
                ->whereNull('students.deleted_at')
                ->select('courses.name as course_name', 'users.email as teacher_email', 'students.status')
                ->get();
                
            foreach ($enrollments as $e) {
                echo "  - Curso: {$e->course_name} | Prof: {$e->teacher_email} | Estado: " . ($e->status ? 'Activo' : 'Inactivo') . "\n";
            }
            echo "------------------------------------------------------------------\n";
        }
    }

} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
