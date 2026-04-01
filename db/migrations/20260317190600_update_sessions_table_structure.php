<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class UpdateSessionsTableStructure extends AbstractMigration
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
        $table = $this->table('sessions');
        
        // Remove old column and foreign key
        if ($table->hasForeignKey('course_group_id')) {
            $table->dropForeignKey('course_group_id');
        }
        
        if ($table->hasColumn('course_group_id')) {
            $table->removeColumn('course_group_id');
        }

        // Add new columns
        $table->addColumn('course_id', 'integer', ['signed' => false, 'null' => true, 'after' => 'period_id'])
              ->addColumn('grade_id', 'integer', ['signed' => false, 'null' => true, 'after' => 'course_id'])
              ->addColumn('classroom_id', 'integer', ['signed' => false, 'null' => true, 'after' => 'grade_id'])
              ->addForeignKey('course_id', 'courses', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
              ->addForeignKey('grade_id', 'grades', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
              ->addForeignKey('classroom_id', 'classrooms', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
              ->update();
    }
}
