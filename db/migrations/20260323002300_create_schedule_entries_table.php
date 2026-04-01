<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateScheduleEntriesTable extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('schedule_entries');
        $table->addColumn('teacher_id', 'integer', ['signed' => false])
              ->addColumn('academic_period_id', 'integer', ['signed' => false])
              ->addColumn('day_of_week', 'smallinteger')
              ->addColumn('start_time', 'time')
              ->addColumn('end_time', 'time')
              ->addColumn('course_id', 'integer', ['signed' => false, 'null' => true])
              ->addColumn('classroom_id', 'integer', ['signed' => false, 'null' => true])
              ->addColumn('is_break', 'boolean', ['default' => false])
              ->addColumn('color', 'string', ['limit' => 50, 'null' => true])
              ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
              ->addColumn('updated_at', 'timestamp', ['null' => true, 'update' => 'CURRENT_TIMESTAMP', 'default' => 'CURRENT_TIMESTAMP'])
              ->addForeignKey('teacher_id', 'teachers', 'id', ['delete'=> 'CASCADE', 'update'=> 'CASCADE'])
              ->addForeignKey('academic_period_id', 'periods', 'id', ['delete'=> 'CASCADE', 'update'=> 'CASCADE'])
              ->addForeignKey('course_id', 'courses', 'id', ['delete'=> 'SET_NULL', 'update'=> 'CASCADE'])
              ->addForeignKey('classroom_id', 'classrooms', 'id', ['delete'=> 'SET_NULL', 'update'=> 'CASCADE'])
              ->create();
    }
}
