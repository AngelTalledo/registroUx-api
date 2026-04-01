<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateAcademicStructureTable extends AbstractMigration
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
        if (!$this->hasTable('academic_years')) {
            $academicYears = $this->table('academic_years');
            $academicYears->addColumn('teacher_id', 'integer')
                          ->addColumn('year', 'integer')
                          ->addColumn('name', 'string', ['limit' => 50])
                          ->addColumn('status', 'boolean', ['default' => true])
                          ->addForeignKey('teacher_id', 'teachers', 'id', ['delete'=> 'CASCADE', 'update'=> 'CASCADE'])
                          ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
                          ->addColumn('updated_at', 'timestamp', ['null' => true, 'update' => 'CURRENT_TIMESTAMP', 'default' => 'CURRENT_TIMESTAMP'])
                          ->addColumn('deleted_at', 'timestamp', ['null' => true])
                          ->create();
        }

        if (!$this->hasTable('periods')) {
            $periods = $this->table('periods');
            $periods->addColumn('academic_year_id', 'integer')
                    ->addColumn('name', 'string', ['limit' => 50])
                    ->addColumn('is_current', 'boolean', ['default' => false])
                    ->addColumn('start_date', 'date')
                    ->addColumn('end_date', 'date')
                    ->addColumn('status', 'boolean', ['default' => true])   
                    ->addForeignKey('academic_year_id', 'academic_years', 'id', ['delete'=> 'CASCADE', 'update'=> 'CASCADE'])
                    ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
                    ->addColumn('updated_at', 'timestamp', ['null' => true, 'update' => 'CURRENT_TIMESTAMP', 'default' => 'CURRENT_TIMESTAMP'])
                    ->addColumn('deleted_at', 'timestamp', ['null' => true])
                    ->create();
        }
    }
}
