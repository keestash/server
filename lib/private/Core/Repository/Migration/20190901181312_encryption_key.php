<?php

use Keestash\Core\Repository\Migration\Base\KeestashMigration;
use Phinx\Migration\AbstractMigration;

class EncryptionKey extends AbstractMigration {

    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-abstractmigration-class
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
    public function change() {
        $this->table("key")
            ->addColumn(
                "value"
                , "blob"
                , [
                    "null" => false
                ]
            )
            ->addColumn(
                "create_ts"
                , KeestashMigration::DATETIME
                , [
                    "null"    => false
                    , "after" => "value"
                    , "default" => "CURRENT_TIMESTAMP"
                ]
            )
            ->save();

        $this->table("user_key")
            ->addColumn(
                "user_id"
                , "integer"
                , [
                    "null" => false
                    , 'signed' => false
                ]
            )
            ->addColumn(
                "key_id"
                , "integer"
                , [
                    "null"    => false
                    , "after" => "user_id"
                    , 'signed' => false
                ]
            )
            ->addColumn(
                "create_ts"
                , KeestashMigration::DATETIME
                , [
                    "null"    => false
                    , "after" => "key_id"
                    , "default" => "CURRENT_TIMESTAMP"
                ]
            )
            ->addForeignKey(
                "user_id"
                , "user"
                , "id"
                , [
                    'delete'   => 'CASCADE'
                    , 'update' => 'CASCADE'
                ]
            )
            ->addForeignKey(
                "key_id"
                , "key"
                , "id"
                , [
                    'delete'   => 'CASCADE'
                    , 'update' => 'CASCADE'
                ]
            )
            ->save();

    }

}
