<?php

use Keestash\Core\Repository\Migration\Base\KeestashMigration;

class File extends KeestashMigration {

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

        $this->table("file")
            ->addColumn(
                "name"
                , "string"
                , [
                    "null"      => false
                    , "comment" => "the files name"
                ]
            )
            ->addColumn(
                "directory"
                , "text"
                , [
                    "null"      => false
                    , "comment" => "The files directory in which the file lies"
                ]
            )
            ->addColumn(
                "path"
                , "text"
                , [
                    "null"      => false
                    , "comment" => "The files full path"
                ]
            )
            ->addColumn(
                "mime_type"
                , "string"
                , [
                    "null"      => false
                    , "comment" => "The files mime type"
                ]
            )
            ->addColumn(
                "hash"
                , "string"
                , [
                    "null"      => false
                    , "comment" => "The files hash"
                ]
            )
            ->addColumn(
                "extension"
                , "string"
                , [
                    "null"      => false
                    , "comment" => "The files extension"
                ]
            )
            ->addColumn(
                "size"
                , "integer"
                , [
                    "null"      => false
                    , "comment" => "The files size"
                ]
            )
            ->addColumn(
                "user_id"
                , "integer"
                , [
                    "null"      => false
                    , "comment" => "The users id who uploaded the file"
                    , 'signed' => false
                ]
            )
            ->addColumn(
                "create_ts"
                , "datetime"
                , [
                    "null"      => false
                    , "comment" => "The timestamp of creation"
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
            ->save();

    }

}
