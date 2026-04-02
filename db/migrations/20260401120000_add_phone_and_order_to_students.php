<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddPhoneAndOrderToStudents extends AbstractMigration
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
        $table = $this->table('students');
        $table->addColumn('phone_number', 'string', ['limit' => 20, 'null' => true, 'after' => 'last_names'])
              ->addColumn('order_number', 'integer', ['null' => true, 'after' => 'phone_number'])
              ->update();
    }
}
