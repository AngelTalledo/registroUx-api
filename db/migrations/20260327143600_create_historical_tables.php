<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateHistoricalTables extends AbstractMigration
{
    public function change(): void
    {
        // 1. historical_evaluations (Promedio de la Capacidad/Competencia)
        if (!$this->hasTable('historical_evaluations')) {
            $table = $this->table('historical_evaluations');
            $table->addColumn('academic_year_id', 'integer', ['signed' => false])
                  ->addColumn('period_id', 'integer', ['signed' => false])
                  ->addColumn('student_id', 'integer', ['signed' => false])
                  ->addColumn('course_id', 'integer', ['signed' => false])
                  ->addColumn('classroom_id', 'integer', ['signed' => false])
                  ->addColumn('competency_id', 'integer', ['signed' => false])
                  ->addColumn('competency_name', 'string', ['limit' => 255])
                  ->addColumn('final_grade', 'string', ['limit' => 10])
                  ->addColumn('is_exonerated', 'boolean', ['default' => false])
                  ->addColumn('teacher_comment', 'text', ['null' => true])
                  ->addColumn('closing_date', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
                  
                  ->addForeignKey('academic_year_id', 'academic_years', 'id', ['delete'=> 'CASCADE', 'update'=> 'CASCADE'])
                  ->addForeignKey('period_id', 'periods', 'id', ['delete'=> 'CASCADE', 'update'=> 'CASCADE'])
                  ->addForeignKey('student_id', 'students', 'id', ['delete'=> 'CASCADE', 'update'=> 'CASCADE'])
                  ->addForeignKey('course_id', 'courses', 'id', ['delete'=> 'CASCADE', 'update'=> 'CASCADE'])
                  ->addForeignKey('classroom_id', 'classrooms', 'id', ['delete'=> 'CASCADE', 'update'=> 'CASCADE'])
                  ->addForeignKey('competency_id', 'competencies', 'id', ['delete'=> 'CASCADE', 'update'=> 'CASCADE'])
                  
                  ->addIndex(['period_id', 'student_id', 'course_id'], ['name' => 'idx_hist_eval_main'])
                  ->create();
        }

        // 2. historical_session_evaluations (Notas por Sesiones)
        if (!$this->hasTable('historical_session_evaluations')) {
            $table = $this->table('historical_session_evaluations');
            $table->addColumn('historical_evaluation_id', 'integer', ['signed' => false])
                  ->addColumn('session_competency_id', 'integer', ['signed' => false])
                  ->addColumn('grade', 'string', ['limit' => 10])
                  ->addColumn('session_label', 'string', ['limit' => 50, 'null' => true])
                  ->addColumn('session_date', 'date', ['null' => true])
                  ->addColumn('session_theme', 'string', ['limit' => 255, 'null' => true])
                  
                  ->addForeignKey('historical_evaluation_id', 'historical_evaluations', 'id', ['delete'=> 'CASCADE', 'update'=> 'CASCADE'])
                  ->addForeignKey('session_competency_id', 'session_competencies', 'id', ['delete'=> 'CASCADE', 'update'=> 'CASCADE'])
                  
                  ->addIndex(['historical_evaluation_id'], ['name' => 'idx_hist_sess_eval_parent'])
                  ->create();
        }

        // 3. historical_attendance (Asistencia Sumarizada)
        if (!$this->hasTable('historical_attendance')) {
            $table = $this->table('historical_attendance');
            $table->addColumn('academic_year_id', 'integer', ['signed' => false])
                  ->addColumn('period_id', 'integer', ['signed' => false])
                  ->addColumn('student_id', 'integer', ['signed' => false])
                  ->addColumn('course_id', 'integer', ['signed' => false])
                  ->addColumn('total_sessions', 'integer', ['default' => 0])
                  ->addColumn('total_presents', 'integer', ['default' => 0])
                  ->addColumn('total_absents', 'integer', ['default' => 0])
                  ->addColumn('total_tardies', 'integer', ['default' => 0])
                  ->addColumn('total_justified', 'integer', ['default' => 0])
                  ->addColumn('closing_date', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
                  
                  ->addForeignKey('academic_year_id', 'academic_years', 'id', ['delete'=> 'CASCADE', 'update'=> 'CASCADE'])
                  ->addForeignKey('period_id', 'periods', 'id', ['delete'=> 'CASCADE', 'update'=> 'CASCADE'])
                  ->addForeignKey('student_id', 'students', 'id', ['delete'=> 'CASCADE', 'update'=> 'CASCADE'])
                  ->addForeignKey('course_id', 'courses', 'id', ['delete'=> 'CASCADE', 'update'=> 'CASCADE'])
                  
                  ->addIndex(['period_id', 'student_id'], ['name' => 'idx_hist_att_main'])
                  ->create();
        }
    }
}
