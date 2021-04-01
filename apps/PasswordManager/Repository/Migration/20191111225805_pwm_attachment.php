<?php

use Keestash\Core\Repository\Migration\Base\KeestashMigration;
use KSA\PasswordManager\Entity\File\NodeFile;

class PwmAttachment extends KeestashMigration {

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
        $this->table("pwm_node_file")
            ->addColumn(
                "node_id"
                , "integer"
                , [
                    "null"      => false
                    , "comment" => "the node's id"
                ]
            )
            ->addColumn(
                "file_id"
                , "integer"
                , [
                    "null"      => false
                    , "comment" => "The file id"
                ]
            )
            ->addColumn(
                "type"
                , "enum"
                , [
                    "null"      => false
                    , "values"  => [
                        NodeFile::FILE_TYPE_AVATAR
                        , NodeFile::FILE_TYPE_ATTACHMENT
                    ]
                    , "default" => NodeFile::FILE_TYPE_ATTACHMENT
                    , "comment" => "The type of the file"
                ]
            )
            ->addColumn(
                "create_ts"
                , "datetime"
                , [
                    "null"      => false
                    , "comment" => "The timestamp of creation"
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
                "file_id"
                , "file"
                , "id"
                , [
                    'delete'   => 'CASCADE'
                    , 'update' => 'CASCADE'
                ]
            )
            ->save();


    }

}
