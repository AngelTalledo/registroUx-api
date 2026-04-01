<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

class CreateInstitutionLogosTable extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('institution_logos');
        $table->addColumn('institution_id', 'integer', ['signed' => false, 'null' => false])
              ->addColumn('url', 'text', ['null' => false])
              ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
              ->addColumn('updated_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP'])
              ->addForeignKey('institution_id', 'institutions', 'id', ['delete' => 'CASCADE'])
              ->create();
    }
}
