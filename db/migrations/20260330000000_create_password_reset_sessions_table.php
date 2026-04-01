<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreatePasswordResetSessionsTable extends AbstractMigration
{
    public function change(): void
    {
        if (!$this->hasTable('password_reset_sessions')) {
            $table = $this->table('password_reset_sessions');
            $table->addColumn('user_id', 'integer', ['signed' => false])
                  ->addColumn('session_token', 'string', ['limit' => 100])
                  ->addColumn('otp_code', 'string', ['limit' => 255])
                  ->addColumn('status', 'enum', ['values' => ['pending', 'scanned', 'verified', 'used', 'expired'], 'default' => 'pending'])
                  ->addColumn('attempts', 'integer', ['default' => 0])
                  ->addColumn('ip_address', 'string', ['limit' => 45, 'null' => true])
                  ->addColumn('user_agent', 'text', ['null' => true])
                  ->addColumn('expires_at', 'timestamp')
                  ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
                  
                  ->addIndex(['session_token'], ['unique' => true, 'name' => 'uq_session_token'])
                  ->addIndex(['user_id'], ['name' => 'idx_user'])
                  ->addIndex(['status'], ['name' => 'idx_status'])
                  ->addIndex(['expires_at'], ['name' => 'idx_expires'])
                  
                  ->addForeignKey('user_id', 'users', 'id', ['delete'=> 'CASCADE', 'update'=> 'CASCADE'])
                  ->create();
        }
    }
}
