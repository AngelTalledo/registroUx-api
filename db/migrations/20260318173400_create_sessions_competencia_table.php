<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateSessionsCompetenciaTable extends AbstractMigration
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
        $table = $this->table('session_competencies');
        $table->addColumn('competency_id', 'integer', ['signed' => false])
              ->addColumn('teacher_id', 'integer', ['signed' => false])
              ->addColumn('period_id', 'integer', ['signed' => false])
              ->addColumn('course_id', 'integer', ['signed' => false])
              ->addColumn('grade_id', 'integer', ['signed' => false])
              ->addColumn('classroom_id', 'integer', ['signed' => false])
              ->addColumn('date', 'date')
              ->addColumn('theme', 'string', ['null' => true])
              ->addColumn('type', 'string', ['null' => true])
              ->addForeignKey('competency_id', 'competencies', 'id', ['delete'=> 'CASCADE', 'update'=> 'CASCADE'])
              ->addForeignKey('teacher_id', 'teachers', 'id', ['delete'=> 'CASCADE', 'update'=> 'CASCADE'])
              ->addForeignKey('period_id', 'periods', 'id', ['delete'=> 'CASCADE', 'update'=> 'CASCADE'])
              ->addForeignKey('course_id', 'courses', 'id', ['delete'=> 'CASCADE', 'update'=> 'CASCADE'])
              ->addForeignKey('grade_id', 'grades', 'id', ['delete'=> 'CASCADE', 'update'=> 'CASCADE'])
              ->addForeignKey('classroom_id', 'classrooms', 'id', ['delete'=> 'CASCADE', 'update'=> 'CASCADE'])
              ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
              ->addColumn('updated_at', 'timestamp', ['null' => true, 'update' => 'CURRENT_TIMESTAMP', 'default' => 'CURRENT_TIMESTAMP'])
              ->addColumn('deleted_at', 'timestamp', ['null' => true])
              ->create();
    }
}
