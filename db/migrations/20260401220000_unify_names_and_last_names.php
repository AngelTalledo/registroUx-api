<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class UnifyNamesAndLastNames extends AbstractMigration
{
    public function up(): void
    {
        // 1. Students
        $students = $this->table('students');
        $students->addColumn('full_name', 'string', ['limit' => 255, 'null' => true, 'after' => 'dni'])
                 ->update();

        $this->execute("UPDATE students SET full_name = CONCAT(last_names, ', ', names)");

        $students->removeColumn('names')
                 ->removeColumn('last_names')
                 ->changeColumn('full_name', 'string', ['limit' => 255, 'null' => false])
                 ->update();

        // 2. Teachers
        $teachers = $this->table('teachers');
        $teachers->addColumn('full_name', 'string', ['limit' => 255, 'null' => true, 'after' => 'user_id'])
                 ->update();

        $this->execute("UPDATE teachers SET full_name = CONCAT(last_names, ', ', names)");

        $teachers->removeColumn('names')
                 ->removeColumn('last_names')
                 ->changeColumn('full_name', 'string', ['limit' => 255, 'null' => false])
                 ->update();
    }

    public function down(): void
    {
        // Students
        $students = $this->table('students');
        $students->addColumn('names', 'string', ['limit' => 100, 'null' => true])
                 ->addColumn('last_names', 'string', ['limit' => 100, 'null' => true])
                 ->update();
        
        // This is a rough estimation for reversal
        $this->execute("UPDATE students SET last_names = SUBSTRING_INDEX(full_name, ', ', 1), names = SUBSTRING_INDEX(full_name, ', ', -1)");
        
        $students->removeColumn('full_name')->update();

        // Teachers
        $teachers = $this->table('teachers');
        $teachers->addColumn('names', 'string', ['limit' => 100, 'null' => true])
                 ->addColumn('last_names', 'string', ['limit' => 100, 'null' => true])
                 ->update();

        $this->execute("UPDATE teachers SET last_names = SUBSTRING_INDEX(full_name, ', ', 1), names = SUBSTRING_INDEX(full_name, ', ', -1)");

        $teachers->removeColumn('full_name')->update();
    }
}
