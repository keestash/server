<?php

use Keestash\Core\Repository\Migration\Base\KeestashMigration;

class Session extends KeestashMigration {

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

        $this->table(
            "session"
            , [
                "id"            => false
                , "primary_key" => "id"
            ]
        )
            ->addColumn(
                "id"
                , "string"
                , [
                    "null"      => false
                    , "comment" => "the session id"
                ]
            )
            ->addColumn(
                "data"
                , "text"
                , [
                    "null"      => false
                    , "comment" => "the session data"
                    , "after"   => "id"
                ]
            )
            ->addColumn(
                "update_ts"
                , "datetime"
                , [
                    "null"      => false
                    , "comment" => "the records last insert / update ts"
                    , "after"   => "data"
                ]
            )
            ->save();

    }

}
