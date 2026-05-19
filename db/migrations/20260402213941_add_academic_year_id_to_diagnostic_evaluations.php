<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddAcademicYearIdToDiagnosticEvaluations extends AbstractMigration
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-change-method
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function up(): void
    {
        $table = $this->table('diagnostic_evaluations');
        
        // 1. Add column if not exists
        if (!$table->hasColumn('academic_year_id')) {
            $table->addColumn('academic_year_id', 'integer', [
                'signed' => false, 
                'null' => true, 
                'after' => 'teacher_id'
            ])->update();
        }

        // 2. Populate academic_year_id from periods table for existing records
        $this->execute("
            UPDATE diagnostic_evaluations de
            JOIN periods p ON de.period_id = p.id
            SET de.academic_year_id = p.academic_year_id
            WHERE de.academic_year_id IS NULL OR de.academic_year_id = 0
        ");

        // 3. Make column NOT NULL now that it's populated
        $table->changeColumn('academic_year_id', 'integer', [
            'signed' => false, 
            'null' => false
        ])->update();

        // 4. Update Index (Ensure it's the 7-column one)
        if ($table->hasIndexByName('unique_diagnostic_evaluation')) {
            $table->removeIndexByName('unique_diagnostic_evaluation')->update();
        }

        $table->addIndex(['teacher_id', 'academic_year_id', 'period_id', 'student_id', 'competency_id', 'course_id', 'aula_id'], [
            'unique' => true, 
            'name' => 'unique_diagnostic_evaluation'
        ])->update();

        // 5. Add Foreign Key if not exists
        // Check if FK exists first to avoid duplicate FK error
        $this->execute("SET FOREIGN_KEY_CHECKS=0");
        try {
            $table->addForeignKey('academic_year_id', 'academic_years', 'id', [
                'delete'=> 'CASCADE', 
                'update'=> 'CASCADE'
            ])->update();
        } catch (\Exception $e) {
            // Probably already exists
        }
        $this->execute("SET FOREIGN_KEY_CHECKS=1");
    }

    public function down(): void
    {
        $table = $this->table('diagnostic_evaluations');
        
        if ($table->hasIndex(['teacher_id', 'academic_year_id', 'period_id', 'student_id', 'competency_id', 'course_id', 'aula_id'])) {
            $table->removeIndexByName('unique_diagnostic_evaluation')->update();
        }

        if ($table->hasColumn('academic_year_id')) {
            // Restore old index first
            $table->addIndex(['teacher_id', 'period_id', 'student_id', 'competency_id', 'course_id', 'aula_id'], [
                'unique' => true, 
                'name' => 'unique_diagnostic_evaluation'
            ])->update();

            $table->dropForeignKey('academic_year_id')->update();
            $table->removeColumn('academic_year_id')->update();
        }
    }
}
