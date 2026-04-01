<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateDailyRegistryTable extends AbstractMigration
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
        $sessions = $this->table('sessions');
        $sessions->addColumn('teacher_id', 'integer', ['signed' => false])
                 ->addColumn('period_id', 'integer', ['signed' => false])
                 ->addColumn('course_group_id', 'integer', ['signed' => false])
                 ->addColumn('date', 'date')
                 ->addColumn('theme', 'string', ['limit' => 255])
                 ->addColumn('type', 'string', ['limit' => 50])
                 ->addForeignKey('teacher_id', 'teachers', 'id', ['delete'=> 'CASCADE', 'update'=> 'CASCADE'])
                 ->addForeignKey('period_id', 'periods', 'id', ['delete'=> 'CASCADE', 'update'=> 'CASCADE'])
                 ->addForeignKey('course_group_id', 'course_groups', 'id', ['delete'=> 'CASCADE', 'update'=> 'CASCADE'])
                 ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
                 ->addColumn('updated_at', 'timestamp', ['null' => true, 'update' => 'CURRENT_TIMESTAMP', 'default' => 'CURRENT_TIMESTAMP'])
                 ->addColumn('deleted_at', 'timestamp', ['null' => true])
                 ->create();

        $attendances = $this->table('attendances');
        $attendances->addColumn('teacher_id', 'integer', ['signed' => false])
                    ->addColumn('session_id', 'integer', ['signed' => false])
                    ->addColumn('student_id', 'integer', ['signed' => false])
                    ->addColumn('status', 'enum', ['values' => ['PRESENTE', 'FALTA', 'TARDANZA']])
                    ->addForeignKey('teacher_id', 'teachers', 'id', ['delete'=> 'CASCADE', 'update'=> 'CASCADE'])
                    ->addForeignKey('session_id', 'sessions', 'id', ['delete'=> 'CASCADE', 'update'=> 'CASCADE'])
                    ->addForeignKey('student_id', 'students', 'id', ['delete'=> 'CASCADE', 'update'=> 'CASCADE'])
                    ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
                    ->addColumn('updated_at', 'timestamp', ['null' => true, 'update' => 'CURRENT_TIMESTAMP', 'default' => 'CURRENT_TIMESTAMP'])
                    ->addColumn('deleted_at', 'timestamp', ['null' => true])
                    ->create();

        $evaluations = $this->table('evaluations');
        $evaluations->addColumn('teacher_id', 'integer', ['signed' => false])
                    ->addColumn('session_id', 'integer', ['signed' => false])
                    ->addColumn('student_id', 'integer', ['signed' => false])
                    ->addColumn('competency_id', 'integer', ['signed' => false])
                    ->addColumn('grade', 'string', ['limit' => 5])
                    ->addForeignKey('teacher_id', 'teachers', 'id', ['delete'=> 'CASCADE', 'update'=> 'CASCADE'])
                    ->addForeignKey('session_id', 'sessions', 'id', ['delete'=> 'CASCADE', 'update'=> 'CASCADE'])
                    ->addForeignKey('student_id', 'students', 'id', ['delete'=> 'CASCADE', 'update'=> 'CASCADE'])
                    ->addForeignKey('competency_id', 'competencies', 'id', ['delete'=> 'CASCADE', 'update'=> 'CASCADE'])
                    ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
                    ->addColumn('updated_at', 'timestamp', ['null' => true, 'update' => 'CURRENT_TIMESTAMP', 'default' => 'CURRENT_TIMESTAMP'])
                    ->addColumn('deleted_at', 'timestamp', ['null' => true])
                    ->create();

        $evidences = $this->table('evidences');
        $evidences->addColumn('evaluation_id', 'integer', ['signed' => false])
                  ->addColumn('file_url', 'text')
                  ->addForeignKey('evaluation_id', 'evaluations', 'id', ['delete'=> 'CASCADE', 'update'=> 'CASCADE'])
                  ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
                  ->addColumn('updated_at', 'timestamp', ['null' => true, 'update' => 'CURRENT_TIMESTAMP', 'default' => 'CURRENT_TIMESTAMP'])
                  ->addColumn('deleted_at', 'timestamp', ['null' => true])
                  ->create();
    }
}
