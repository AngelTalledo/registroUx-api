<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateUsersAndTeachersTable extends AbstractMigration
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
        if (!$this->hasTable('users')) {
            $users = $this->table('users');
            $users->addColumn('email', 'string', ['limit' => 100])
                  ->addColumn('password', 'string', ['limit' => 255])
                  ->addIndex(['email'], ['unique' => true])
                  ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
                  ->addColumn('updated_at', 'timestamp', ['null' => true, 'update' => 'CURRENT_TIMESTAMP', 'default' => 'CURRENT_TIMESTAMP'])
                  ->addColumn('deleted_at', 'timestamp', ['null' => true])
                  ->create();
        }

        if (!$this->hasTable('teachers')) {
            $teachers = $this->table('teachers');
            $teachers->addColumn('user_id', 'integer')
                     ->addColumn('names', 'string', ['limit' => 100])
                     ->addColumn('last_names', 'string', ['limit' => 100])
                     ->addForeignKey('user_id', 'users', 'id', ['delete'=> 'CASCADE', 'update'=> 'CASCADE'])
                     ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
                     ->addColumn('updated_at', 'timestamp', ['null' => true, 'update' => 'CURRENT_TIMESTAMP', 'default' => 'CURRENT_TIMESTAMP'])
                     ->addColumn('deleted_at', 'timestamp', ['null' => true])
                     ->create();
        }
    }
}
