<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateAcademicLoadTable extends AbstractMigration
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
        $students = $this->table('students');
        $students->addColumn('teacher_id', 'integer', ['signed' => false])
                 ->addColumn('classroom_id', 'integer', ['signed' => false])
                 ->addColumn('course_id', 'integer', ['signed' => false])
                 ->addColumn('grade_id', 'integer', ['signed' => false])
                 ->addColumn('dni', 'string', ['limit' => 20])
                 ->addColumn('names', 'string', ['limit' => 100])
                 ->addColumn('last_names', 'string', ['limit' => 100])
                 ->addColumn('gender', 'string', ['limit' => 20, 'null' => true])
                 ->addColumn('status', 'boolean', ['default' => true])
                 ->addForeignKey('teacher_id', 'teachers', 'id', ['delete'=> 'CASCADE', 'update'=> 'CASCADE'])
                 ->addForeignKey('classroom_id', 'classrooms', 'id', ['delete'=> 'CASCADE', 'update'=> 'CASCADE'])
                 ->addForeignKey('course_id', 'courses', 'id', ['delete'=> 'CASCADE', 'update'=> 'CASCADE'])
                 ->addForeignKey('grade_id', 'grades', 'id', ['delete'=> 'CASCADE', 'update'=> 'CASCADE'])
                 ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
                 ->addColumn('updated_at', 'timestamp', ['null' => true, 'update' => 'CURRENT_TIMESTAMP', 'default' => 'CURRENT_TIMESTAMP'])
                 ->addColumn('deleted_at', 'timestamp', ['null' => true])
                 ->create();
    }
}
