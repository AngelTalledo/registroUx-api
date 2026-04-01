<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateScheduleSettingsTable extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('schedule_settings');
        $table->addColumn('teacher_id', 'integer', ['signed' => false])
              ->addColumn('academic_year_id', 'integer', ['signed' => false])
              ->addColumn('start_time', 'time', ['default' => '08:00:00'])
              ->addColumn('end_time', 'time', ['default' => '18:00:00'])
              ->addColumn('slot_duration', 'integer', ['default' => 60, 'comment' => 'duration in minutes'])
              ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
              ->addColumn('updated_at', 'timestamp', ['null' => true, 'update' => 'CURRENT_TIMESTAMP', 'default' => 'CURRENT_TIMESTAMP'])
              ->addForeignKey('teacher_id', 'teachers', 'id', ['delete'=> 'CASCADE', 'update'=> 'CASCADE'])
              ->addForeignKey('academic_year_id', 'academic_years', 'id', ['delete'=> 'RESTRICT', 'update'=> 'CASCADE'])
              ->addIndex(['teacher_id', 'academic_year_id'], ['unique' => true])
              ->create();
    }
}
