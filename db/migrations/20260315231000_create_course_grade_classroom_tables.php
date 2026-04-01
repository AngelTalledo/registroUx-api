<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateCourseGradeClassroomTables extends AbstractMigration
{
    public function change(): void
    {
        // Table: grades
        if (!$this->hasTable('grades')) {
            $table = $this->table('grades');
            $table->addColumn('teacher_id', 'integer', ['signed' => false])
                  ->addColumn('name', 'string', ['limit' => 50])
                  ->addColumn('status', 'boolean', ['default' => true])
                  ->addForeignKey('teacher_id', 'teachers', 'id', ['delete'=> 'CASCADE', 'update'=> 'CASCADE'])
                  ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
                  ->addColumn('updated_at', 'timestamp', ['null' => true, 'update' => 'CURRENT_TIMESTAMP', 'default' => 'CURRENT_TIMESTAMP'])
                  ->addColumn('deleted_at', 'timestamp', ['null' => true])
                  ->create();
        }

        // Table: courses
        if (!$this->hasTable('courses')) {
            $table = $this->table('courses');
            $table->addColumn('teacher_id', 'integer', ['signed' => false])
                  ->addColumn('academic_year_id', 'integer', ['signed' => false])
                  ->addColumn('name', 'string', ['limit' => 50])
                  ->addColumn('status', 'boolean', ['default' => true])
                  ->addForeignKey('teacher_id', 'teachers', 'id', ['delete'=> 'CASCADE', 'update'=> 'CASCADE'])
                  ->addForeignKey('academic_year_id', 'academic_years', 'id', ['delete'=> 'CASCADE', 'update'=> 'CASCADE'])
                  ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
                  ->addColumn('updated_at', 'timestamp', ['null' => true, 'update' => 'CURRENT_TIMESTAMP', 'default' => 'CURRENT_TIMESTAMP'])
                  ->addColumn('deleted_at', 'timestamp', ['null' => true])
                  ->create();
        }

        if (!$this->hasTable('classrooms')) {
            $table = $this->table('classrooms');
            $table->addColumn('teacher_id', 'integer', ['signed' => false])
                  ->addColumn('academic_year_id', 'integer', ['signed' => false])
                  ->addColumn('course_id', 'integer', ['signed' => false])
                  ->addColumn('grade_id', 'integer', ['signed' => false])
                  ->addColumn('section', 'string', ['limit' => 10])
                  ->addColumn('status', 'boolean', ['default' => true])
                  ->addForeignKey('teacher_id', 'teachers', 'id', ['delete'=> 'CASCADE', 'update'=> 'CASCADE'])
                  ->addForeignKey('academic_year_id', 'academic_years', 'id', ['delete'=> 'CASCADE', 'update'=> 'CASCADE'])
                  ->addForeignKey('course_id', 'courses', 'id', ['delete'=> 'CASCADE', 'update'=> 'CASCADE'])
                  ->addForeignKey('grade_id', 'grades', 'id', ['delete'=> 'CASCADE', 'update'=> 'CASCADE'])
                  ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
                  ->addColumn('updated_at', 'timestamp', ['null' => true, 'update' => 'CURRENT_TIMESTAMP', 'default' => 'CURRENT_TIMESTAMP'])
                  ->addColumn('deleted_at', 'timestamp', ['null' => true])
                  ->create();
        }
    }
}
