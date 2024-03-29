<?php

use Keestash\Core\Repository\Migration\Base\KeestashMigration;

class DemoUsers extends KeestashMigration {

    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html
     *
     * The following commands can be used in this method and Phinx will
     * automatically reverse them when rolling back:
     *
     *    createTable
     *    renameTable
     *    addColumn
     *    addCustomColumn
     *    renameColumn
     *    addIndex
     *    addForeignKey
     *
     * Any other destructive changes will result in an error when trying to
     * rollback the migration.
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function change(): void {
        if ($this->table('demo_users')->exists()) {
            return;
        }
        $this->table("demo_users")
            ->addColumn(
                "email"
                , KeestashMigration::STRING
                , [
                    "null"      => false
                    , "comment" => "The users email address"
                ]
            )
            ->addColumn(
                "create_ts"
                , "datetime"
                , [
                    "null"      => false
                    , "comment" => "The organization's create timestamp"
                    , "default" => "CURRENT_TIMESTAMP"
                ]
            )
            ->save();
    }

}
