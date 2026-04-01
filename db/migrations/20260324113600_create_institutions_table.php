<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

class CreateInstitutionsTable extends AbstractMigration
{
    public function up(): void
    {
        if ($this->hasTable('institutions')) {
            $this->table('institutions')->drop()->save();
        }

        $table = $this->table('institutions');
        $table->addColumn('name', 'string', ['limit' => 255, 'null' => false])
              ->addColumn('template_id', 'integer', ['signed' => false, 'null' => true])
              ->addColumn('report_enabled', 'boolean', ['default' => true])
              ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
              ->addColumn('updated_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP'])
              ->addColumn('deleted_at', 'timestamp', ['null' => true])
              ->addForeignKey('template_id', 'header_templates', 'id', ['delete' => 'SET_NULL'])
              ->create();
    }

    public function down(): void
    {
        $this->table('institutions')->drop()->save();
    }
}
