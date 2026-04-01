<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddStatusToCompetenciesTable extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('competencies');
        if ($this->hasTable('competencies')) {
            $table->addColumn('status', 'boolean', [
                'default' => 1,
                'after' => 'name'
            ])
            ->update();
        }
    }
}
