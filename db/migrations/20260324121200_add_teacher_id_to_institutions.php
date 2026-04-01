<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

class AddTeacherIdToInstitutions extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('institutions');
        $table->addColumn('teacher_id', 'integer', ['signed' => false, 'null' => false, 'after' => 'id'])
              ->addForeignKey('teacher_id', 'teachers', 'id', ['delete' => 'CASCADE'])
              ->update();
    }
}
