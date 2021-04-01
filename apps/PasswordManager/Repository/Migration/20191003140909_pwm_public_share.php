<?php

use Keestash\Core\Repository\Migration\Base\KeestashMigration;

class PwmPublicShare extends KeestashMigration {

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

        $this->table("pwm_public_share")
            ->addColumn(
                "node_id"
                , "integer"
                , [
                    "null"      => false
                    , "comment" => "The node that is shared"
                ]
            )
            ->addColumn(
                "hash"
                , "text"
                , [
                    "null"      => false
                    , "comment" => "The hash which identifies the node"
                ]
            )
            ->addColumn(
                "expire_ts"
                , "datetime"
                , [
                    "null"      => false
                    , "comment" => "The datetime where the sharing expires"
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
            ->save();

    }

}
