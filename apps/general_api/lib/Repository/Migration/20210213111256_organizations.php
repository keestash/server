<?php

use Keestash\Core\Repository\Migration\Base\KeestashMigration;

class Organizations extends KeestashMigration {

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
    public function change() {
        $this->table("organization")
            ->addColumn(
                "name"
                , "string"
                , [
                    "null"      => false
                    , "comment" => "The organization's name"
                ]
            )
            ->addColumn(
                "active_ts"
                , "datetime"
                , [
                    "null"      => true
                    , "comment" => "The ts where the organization has been activated"
                    , 'default' => null
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

        $this->table("user_organization")
            ->addColumn(
                "organization_id"
                , KeestashMigration::INTEGER
                , [
                    "null"      => false
                    , "comment" => "The organization id"
                ]
            )
            ->addColumn(
                "user_id"
                , KeestashMigration::INTEGER
                , [
                    "null"      => false
                    , "comment" => "The user id"
                ]
            )
            ->addColumn(
                "create_ts"
                , "datetime"
                , [
                    "null"      => false
                    , "comment" => "The create timestamp"
                    , "default" => "CURRENT_TIMESTAMP"
                ]
            )
            ->addForeignKey(
                "organization_id"
                , "organization"
                , "id"
                , [
                    'delete'   => 'CASCADE'
                    , 'update' => 'CASCADE'
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
            ->save();

        $this->table("organization_key")
            ->addColumn(
                "organization_id"
                , KeestashMigration::INTEGER
                , [
                    "null"      => false
                    , "comment" => "The organization id"
                ]
            )
            ->addColumn(
                "key_id"
                , KeestashMigration::INTEGER
                , [
                    "null"      => false
                    , "comment" => "The key id"
                ]
            )
            ->addColumn(
                "create_ts"
                , "datetime"
                , [
                    "null"      => false
                    , "comment" => "The create timestamp"
                    , "default" => "CURRENT_TIMESTAMP"
                ]
            )
            ->addForeignKey(
                "organization_id"
                , "organization"
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
