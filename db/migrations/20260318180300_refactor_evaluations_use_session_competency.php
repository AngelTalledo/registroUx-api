<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class RefactorEvaluationsUseSessionCompetency extends AbstractMigration
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
        $table = $this->table('evaluations');
        
        // Add the new column if it doesn't exist
        if (!$table->hasColumn('session_competency_id')) {
            $table->addColumn('session_competency_id', 'integer', ['signed' => false, 'null' => true, 'after' => 'teacher_id'])
                  ->addForeignKey('session_competency_id', 'session_competencies', 'id', ['delete'=> 'CASCADE', 'update'=> 'CASCADE'])
                  ->update();
        }

        // Remove old foreign keys and columns
        if ($table->hasForeignKey('session_id')) {
            $table->dropForeignKey('session_id');
        }
        if ($table->hasForeignKey('competency_id')) {
            $table->dropForeignKey('competency_id');
        }

        if ($table->hasColumn('session_id')) {
            $table->removeColumn('session_id');
        }
        if ($table->hasColumn('competency_id')) {
            $table->removeColumn('competency_id');
        }

        $table->update();
    }
}
