<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

class CreateStudentStatusHistoryTable extends AbstractMigration
{
    public function up(): void
    {
        $table = $this->table('student_status_history');
        $table->addColumn('student_id', 'integer', ['signed' => false, 'null' => false])
              ->addColumn('old_status', 'boolean', ['null' => true])
              ->addColumn('new_status', 'boolean', ['null' => false])
              ->addColumn('reason', 'text', ['null' => true])
              ->addColumn('changed_by', 'integer', ['signed' => false, 'null' => true])
              ->addColumn('change_date', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
              ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
              ->addForeignKey('student_id', 'students', 'id', ['delete' => 'CASCADE'])
              ->addForeignKey('changed_by', 'users', 'id', ['delete' => 'SET_NULL'])
              ->create();
    }

    public function down(): void
    {
        $this->table('student_status_history')->drop()->save();
    }
}
