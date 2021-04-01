<?php

use Keestash\Core\Repository\Migration\Base\KeestashMigration;

class PasswordComments extends KeestashMigration {

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
        $this->table("pwm_comment")
            ->addColumn(
                "comment"
                , "text"
                , [
                    "null"      => false
                    , "comment" => "The comment"
                ]
            )
            ->addColumn(
                "node_id"
                , "integer"
                , [
                    "null"      => false
                    , "comment" => "The node getting commented"
                ]
            )
            ->addColumn(
                "user_id"
                , "integer"
                , [
                    "null"      => false
                    , "comment" => "The user that made the comment"
                ]
            )
            ->addColumn(
                "create_ts"
                , "datetime"
                , [
                    "null"      => false
                    , "comment" => "The create ts"
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
                "user_id"
                , "user"
                , "id"
                , [
                    'delete'   => 'CASCADE'
                    , 'update' => 'CASCADE'
                ]
            )
            ->save();
    }

}
