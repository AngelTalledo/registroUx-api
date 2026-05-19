<?php
$pdo = new PDO('mysql:host=localhost;dbname=registroux_db', 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

echo "Starting Database Fix...\n";

try {
    echo "1. Disabling Foreign Key Checks...\n";
    $pdo->exec("SET FOREIGN_KEY_CHECKS=0");

    echo "2. Dropping Foreign Keys from diagnostic_evaluations...\n";
    $fks = [
        'diagnostic_evaluations_ibfk_1',
        'diagnostic_evaluations_ibfk_2',
        'diagnostic_evaluations_ibfk_3',
        'diagnostic_evaluations_ibfk_4',
        'diagnostic_evaluations_ibfk_5',
        'diagnostic_evaluations_ibfk_6',
        'fk_diag_eval_academic_year' // Possible name from previous attempts
    ];

    foreach ($fks as $fk) {
        try {
            $pdo->exec("ALTER TABLE diagnostic_evaluations DROP FOREIGN KEY $fk");
            echo "   Dropped FK: $fk\n";
        } catch (Exception $e) {
            // Ignore if not exists
        }
    }

    echo "3. Dropping Index unique_diagnostic_evaluation...\n";
    try {
        $pdo->exec("ALTER TABLE diagnostic_evaluations DROP INDEX unique_diagnostic_evaluation");
        echo "   Dropped Index: unique_diagnostic_evaluation\n";
    } catch (Exception $e) {
        echo "   Index unique_diagnostic_evaluation already gone or error: " . $e->getMessage() . "\n";
    }

    echo "4. Populating academic_year_id...\n";
    $pdo->exec("UPDATE diagnostic_evaluations de JOIN periods p ON de.period_id = p.id SET de.academic_year_id = p.academic_year_id WHERE de.academic_year_id IS NULL OR de.academic_year_id = 0");

    echo "5. Creating New 7-Column Unique Index...\n";
    $pdo->exec("ALTER TABLE diagnostic_evaluations ADD UNIQUE INDEX unique_diagnostic_evaluation (teacher_id, academic_year_id, period_id, student_id, competency_id, course_id, aula_id)");
    echo "   Created index successfully.\n";

    echo "6. Re-adding Foreign Keys...\n";
    $pdo->exec("ALTER TABLE diagnostic_evaluations ADD CONSTRAINT diagnostic_evaluations_ibfk_1 FOREIGN KEY (teacher_id) REFERENCES teachers(id) ON DELETE CASCADE ON UPDATE CASCADE");
    $pdo->exec("ALTER TABLE diagnostic_evaluations ADD CONSTRAINT diagnostic_evaluations_ibfk_2 FOREIGN KEY (period_id) REFERENCES periods(id) ON DELETE CASCADE ON UPDATE CASCADE");
    $pdo->exec("ALTER TABLE diagnostic_evaluations ADD CONSTRAINT diagnostic_evaluations_ibfk_3 FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE ON UPDATE CASCADE");
    $pdo->exec("ALTER TABLE diagnostic_evaluations ADD CONSTRAINT diagnostic_evaluations_ibfk_4 FOREIGN KEY (competency_id) REFERENCES competencies(id) ON DELETE CASCADE ON UPDATE CASCADE");
    $pdo->exec("ALTER TABLE diagnostic_evaluations ADD CONSTRAINT diagnostic_evaluations_ibfk_5 FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE ON UPDATE CASCADE");
    $pdo->exec("ALTER TABLE diagnostic_evaluations ADD CONSTRAINT diagnostic_evaluations_ibfk_6 FOREIGN KEY (aula_id) REFERENCES classrooms(id) ON DELETE CASCADE ON UPDATE CASCADE");
    $pdo->exec("ALTER TABLE diagnostic_evaluations ADD CONSTRAINT fk_diag_eval_academic_year FOREIGN KEY (academic_year_id) REFERENCES academic_years(id) ON DELETE CASCADE ON UPDATE CASCADE");
    echo "   Added all FKs back.\n";

    echo "7. Enabling Foreign Key Checks...\n";
    $pdo->exec("SET FOREIGN_KEY_CHECKS=1");

    echo "DONE.\n";
} catch (Exception $e) {
    echo "FATAL ERROR: " . $e->getMessage() . "\n";
    $pdo->exec("SET FOREIGN_KEY_CHECKS=1");
}
