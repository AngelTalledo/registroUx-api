<?php
$pdo = new PDO('mysql:host=localhost;dbname=registroux_db', 'root', '');

echo "1. Checking academic_year_id...\n";
$pdo->exec("UPDATE diagnostic_evaluations de JOIN periods p ON de.period_id = p.id SET de.academic_year_id = p.academic_year_id WHERE de.academic_year_id IS NULL OR de.academic_year_id = 0");

echo "2. Dropping old unique index if exists...\n";
try {
    $pdo->exec("ALTER TABLE diagnostic_evaluations DROP INDEX unique_diagnostic_evaluation");
    echo "Dropped unique_diagnostic_evaluation\n";
} catch (Exception $e) {
    echo "Could not drop unique_diagnostic_evaluation (maybe already gone?): " . $e->getMessage() . "\n";
}

echo "3. Creating new unique index (7 columns)...\n";
try {
    $pdo->exec("ALTER TABLE diagnostic_evaluations ADD UNIQUE INDEX unique_diagnostic_evaluation (teacher_id, academic_year_id, period_id, student_id, competency_id, course_id, aula_id)");
    echo "Created new unique index\n";
} catch (Exception $e) {
    echo "Error creating index: " . $e->getMessage() . "\n";
}

echo "4. Marking migration as applied in phinxlog...\n";
// We need the exact version number: 20260402213941
// Check if it exists in phinxlog
$stmt = $pdo->prepare("SELECT count(*) FROM phinxlog WHERE version = ?");
$stmt->execute(['20260402213941']);
if ($stmt->fetchColumn() == 0) {
    $pdo->prepare("INSERT INTO phinxlog (version, migration_name, start_time, end_time, breakpoint) VALUES (?, ?, NOW(), NOW(), 0)")
        ->execute(['20260402213941', 'AddAcademicYearIdToDiagnosticEvaluations']);
    echo "Marked migration as applied.\n";
} else {
    echo "Migration already marked as applied in phinxlog.\n";
}

echo "DONE.\n";
