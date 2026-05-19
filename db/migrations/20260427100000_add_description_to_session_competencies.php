<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddDescriptionToSessionCompetencies extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('session_competencies');
        $table->addColumn('description', 'text', ['null' => true, 'after' => 'theme'])
              ->update();
    }
}
