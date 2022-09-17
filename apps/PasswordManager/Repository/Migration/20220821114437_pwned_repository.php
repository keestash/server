<?php
declare(strict_types=1);

use Keestash\Core\Repository\Migration\Base\KeestashMigration;

final class PwnedRepository extends KeestashMigration {

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
    public function change(): void {
        $this->createPwmPwnedPasswords();
        $this->createPwmPwnedBreaches();
    }

    private function createPwmPwnedPasswords(): void {
        $this->table("pwm_pwned_passwords")
            ->addColumn(
                "node_id"
                , KeestashMigration::INTEGER
                , [
                    "null" => false
                ]
            )
            ->addColumn(
                "severity"
                , KeestashMigration::INTEGER
                , [
                    "null"    => false
                    , "after" => "node_id"
                ]
            )
            ->addColumn(
                "update_ts"
                , KeestashMigration::DATETIME
                , [
                    "null"      => true
                    , "default" => null
                    , "after"   => "severity"
                ]
            )
            ->addColumn(
                "create_ts"
                , KeestashMigration::DATETIME
                , [
                    "null"      => false
                    , "default" => "CURRENT_TIMESTAMP"
                    , "after"   => "update_ts"
                ]
            )
            ->addForeignKey(
                "node_id"
                , "pwm_node"
                , "id"
                , [
                    'delete'   => 'CASCADE'
                    , 'update' => 'CASCADE'
                ]
            )
            ->addIndex(['node_id'], ['unique' => true])
            ->save();
    }

    private function createPwmPwnedBreaches(): void {
        $this->table("pwm_pwned_breaches")
            ->addColumn(
                "node_id"
                , KeestashMigration::INTEGER
                , [
                    "null" => false
                ]
            )
            ->addColumn(
                "hibp_data"
                , KeestashMigration::JSON
                , [
                    "null"      => true
                    , "default" => null
                    , "after"   => "node_id"
                ]
            )
            ->addColumn(
                "update_ts"
                , KeestashMigration::DATETIME
                , [
                    "null"      => true
                    , "default" => null
                    , "after"   => "severity"
                ]
            )
            ->addColumn(
                "create_ts"
                , KeestashMigration::DATETIME
                , [
                    "null"      => false
                    , "default" => "CURRENT_TIMESTAMP"
                    , "after"   => "update_ts"
                ]
            )
            ->addForeignKey(
                "node_id"
                , "pwm_node"
                , "id"
                , [
                    'delete'   => 'CASCADE'
                    , 'update' => 'CASCADE'
                ]
            )
            ->addIndex(['node_id'], ['unique' => true])
            ->save();
    }

}
