<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateCompetenciesTable extends AbstractMigration
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
        if (!$this->hasTable('competencies')) {
            $table = $this->table('competencies');
            $table->addColumn('teacher_id', 'integer', ['signed' => false])
                  ->addColumn('academic_year_id', 'integer', ['signed' => false])
                  ->addColumn('name', 'string', ['limit' => 100])
                  ->addForeignKey('teacher_id', 'teachers', 'id', ['delete'=> 'CASCADE', 'update'=> 'CASCADE'])
                  ->addForeignKey('academic_year_id', 'academic_years', 'id', ['delete'=> 'CASCADE', 'update'=> 'CASCADE'])
                  ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
                  ->addColumn('updated_at', 'timestamp', ['null' => true, 'update' => 'CURRENT_TIMESTAMP', 'default' => 'CURRENT_TIMESTAMP'])
                  ->addColumn('deleted_at', 'timestamp', ['null' => true])
                  ->create();
        }
    }
}
