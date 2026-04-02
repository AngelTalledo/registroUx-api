<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateDiagnosticEvaluationsTable extends AbstractMigration
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
    public function change(): void
    {
        $table = $this->table('diagnostic_evaluations');
        $table->addColumn('teacher_id', 'integer', ['signed' => false, 'null' => false])
              ->addColumn('period_id', 'integer', ['signed' => false, 'null' => false])
              ->addColumn('student_id', 'integer', ['signed' => false, 'null' => false])
              ->addColumn('competency_id', 'integer', ['signed' => false, 'null' => false])
              ->addColumn('course_id', 'integer', ['signed' => false, 'null' => false])
              ->addColumn('aula_id', 'integer', ['signed' => false, 'null' => false])
              ->addColumn('grade', 'string', ['limit' => 2, 'null' => false, 'comment' => 'Possible values: AD, A, B, C'])
              ->addColumn('evaluation_date', 'date', ['null' => false])
              ->addTimestamps()
              ->addColumn('deleted_at', 'timestamp', ['null' => true])
              
              ->addForeignKey('teacher_id', 'teachers', 'id', ['delete'=> 'CASCADE', 'update'=> 'CASCADE'])
              ->addForeignKey('period_id', 'periods', 'id', ['delete'=> 'CASCADE', 'update'=> 'CASCADE'])
              ->addForeignKey('student_id', 'students', 'id', ['delete'=> 'CASCADE', 'update'=> 'CASCADE'])
              ->addForeignKey('competency_id', 'competencies', 'id', ['delete'=> 'CASCADE', 'update'=> 'CASCADE'])
              ->addForeignKey('course_id', 'courses', 'id', ['delete'=> 'CASCADE', 'update'=> 'CASCADE'])
              ->addForeignKey('aula_id', 'classrooms', 'id', ['delete'=> 'CASCADE', 'update'=> 'CASCADE'])
              
              ->addIndex(['teacher_id', 'period_id', 'student_id', 'competency_id', 'course_id', 'aula_id'], ['unique' => true, 'name' => 'unique_diagnostic_evaluation'])
              
              ->create();
    }
}
