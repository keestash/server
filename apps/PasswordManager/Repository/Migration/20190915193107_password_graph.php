<?php

use Keestash\Core\Repository\Migration\Base\KeestashMigration;

class PasswordGraph extends KeestashMigration {

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

        $this->table("pwm_node")
            ->addColumn(
                "name"
                , "string"
                , [
                    "null"      => false
                    , "comment" => "The node's name"
                ]
            )
            ->addColumn(
                "user_id"
                , "integer"
                , [
                    "null"      => false
                    , "comment" => "The user to whom the node belongs to"
                ]
            )
            ->addColumn(
                "type"
                , "enum"
                , [
                    "null"     => false
                    , "values" => [
                        "root"
                        , "folder"
                        , "credential"
                    ]
                ]
            )
            ->addColumn(
                "create_ts"
                , "datetime"
                , [
                    "null"      => false
                    , "default" => "CURRENT_TIMESTAMP"
                ]
            )
            ->addColumn(
                "update_ts"
                , "datetime"
                , [
                    "null"      => true
                    , "default" => null
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

        $this->table("pwm_credential")
            ->addColumn(
                "node_id"
                , "integer"
                , [
                "null" => false
            ])
            ->addColumn(
                "username"
                , "blob"
                , [
                "comment" => "The username to whom the password belongs to"
                , "null"  => false
            ])
            ->addColumn(
                "password"
                , "blob"
                , [
                "comment" => "The password"
                , "null"  => false
            ])
            ->addColumn(
                "url"
                , "blob"
                , [
                "comment" => "The url to which the password belongs to"
                , "null"  => false
            ])
            ->addColumn(
                "note"
                , "blob"
                , [
                    "null"      => true
                    , "comment" => "A comment for the password"
                    , "after"   => "url"
                ]
            )
            ->addColumn(
                "create_ts"
                , "datetime"
                , [
                "comment"   => "The unixtimestamp of the record creation"
                , "null"    => false
                , "default" => "CURRENT_TIMESTAMP"
            ])
            ->addForeignKey(
                "node_id"
                , "pwm_node"
                , "id"
                , [
                    'delete'   => 'CASCADE'
                    , 'update' => 'CASCADE'
                ]
            )
            ->save();

        $this->table("pwm_edge")
            ->addColumn(
                "node_id"
                , "integer"
                , [
                    "null"      => false
                    , "comment" => "The node's ID"
                ]
            )
            ->addColumn(
                "parent_id"
                , "integer"
                , [
                    "null"      => false
                    , "comment" => "The parent node that contains a node"
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
            ->addForeignKey(
                "parent_id"
                , "pwm_node"
                , "id"
                , [
                    'delete'   => 'CASCADE'
                    , 'update' => 'CASCADE'
                ]
            )
            ->save();

    }


}
