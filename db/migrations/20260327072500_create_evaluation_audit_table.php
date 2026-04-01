<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

class CreateEvaluationAuditTable extends AbstractMigration
{
    public function up(): void
    {
        $table = $this->table('evaluation_audit');
        $table->addColumn('evaluation_id', 'integer', ['signed' => false, 'null' => false])
              ->addColumn('old_grade', 'string', ['limit' => 5, 'null' => true])
              ->addColumn('new_grade', 'string', ['limit' => 5, 'null' => false])
              ->addColumn('changed_by', 'integer', ['signed' => false, 'null' => true])
              ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
              ->addForeignKey('evaluation_id', 'evaluations', 'id', ['delete' => 'CASCADE'])
              ->addForeignKey('changed_by', 'users', 'id', ['delete' => 'SET_NULL'])
              ->create();
    }

    public function down(): void
    {
        $this->table('evaluation_audit')->drop()->save();
    }
}
