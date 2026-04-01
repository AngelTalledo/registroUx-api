<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class FixEvaluationsForeignKey extends AbstractMigration
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
        // 1. Fix the referenced table (session_competencies.id) to be unsigned
        $scTable = $this->table('session_competencies');
        $scTable->changeColumn('id', 'integer', [
            'signed' => false, 
            'identity' => true
        ])->update();

        // 2. Fix the evaluations table
        $table = $this->table('evaluations');

        // Drop foreign keys if they exist
        if ($table->hasForeignKey('session_competency_id')) {
            $table->dropForeignKey('session_competency_id');
        }
        if ($table->hasForeignKey('competency_id')) {
            $table->dropForeignKey('competency_id');
        }

        // Drop the redundant competency_id column
        if ($table->hasColumn('competency_id')) {
            $table->removeColumn('competency_id');
        }

        // Add the correct foreign key
        $table->addForeignKey('session_competency_id', 'session_competencies', 'id', [
            'delete' => 'CASCADE',
            'update' => 'CASCADE'
        ]);

        $table->update();
    }
}
