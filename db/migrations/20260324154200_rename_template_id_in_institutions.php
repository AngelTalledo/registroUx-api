<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class RenameTemplateIdInInstitutions extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('institutions');
        $table->renameColumn('template_id', 'header_template_id')
              ->update();
    }
}
