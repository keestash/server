<?php

use Keestash\Core\Repository\Migration\Base\KeestashMigration;
use KSA\PasswordManager\Entity\Edge\Edge;

class PwmShare extends KeestashMigration {

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
        $this->table("pwm_edge")
            ->addColumn(
                "type"
                , "enum"
                , [
                    "null"      => false
                    , "default" => Edge::TYPE_REGULAR
                    , "values"  => [
                        Edge::TYPE_SHARE
                        , Edge::TYPE_REGULAR
                        , Edge::TYPE_ORGANIZATION
                    ]
                    , "comment" => "Whether the edge is a share or not"
                ]
            )
            ->addColumn(
                "expire_ts"
                , "datetime"
                , [
                    "null"      => true
                    , "default" => null
                    , "comment" => "The date of being expired"
                ]
            )
            ->save();
    }

}
