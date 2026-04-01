<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddNameToInstitutionLogos extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('institution_logos');
        $table->addColumn('name', 'string', ['limit' => 255, 'after' => 'institution_id', 'null' => true])
              ->update();
    }
}
