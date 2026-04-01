<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

class CreateHeaderTemplatesTable extends AbstractMigration
{
    public function up(): void
    {
        if ($this->hasTable('header_templates')) {
            $this->table('header_templates')->drop()->save();
        }

        $table = $this->table('header_templates');
        $table->addColumn('teacher_id', 'integer', ['signed' => false, 'null' => false])
              ->addColumn('name', 'string', ['limit' => 100, 'null' => false])
              ->addColumn('description', 'text', ['null' => true])
              ->addColumn('type', 'string', ['limit' => 50, 'null' => true])
              ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
              ->addColumn('updated_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP'])
              ->addColumn('deleted_at', 'timestamp', ['null' => true])
              ->addForeignKey('teacher_id', 'teachers', 'id', ['delete' => 'CASCADE'])
              ->create();
    }

    public function down(): void
    {
        $this->table('header_templates')->drop()->save();
    }
}
