<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddCourseIdToCompetenciesTable extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('competencies');
        if ($this->hasTable('competencies')) {
            $table->addColumn('course_id', 'integer', [
                'signed' => false,
                'null' => true,
                'after' => 'academic_year_id'
            ])
            ->addForeignKey('course_id', 'courses', 'id', [
                'delete'=> 'SET_NULL', 
                'update'=> 'CASCADE'
            ])
            ->update();
        }
    }
}
