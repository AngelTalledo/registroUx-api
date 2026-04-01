<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddGenderToTeachersTable extends AbstractMigration
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-change-method
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function change(): void
    {
        $table = $this->table('teachers');
        $table->addColumn('gender', 'char', [
            'limit' => 1,
            'null' => true,
            'comment' => 'F: Femenino, M: Masculino',
            'after' => 'last_names'
        ])->update();
    }
}
