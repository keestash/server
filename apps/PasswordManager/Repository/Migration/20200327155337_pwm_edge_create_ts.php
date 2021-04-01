<?php

use Keestash\Core\Repository\Migration\Base\KeestashMigration;
use Phinx\Db\Adapter\MysqlAdapter;

class PwmEdgeCreateTs extends KeestashMigration {

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
                "create_ts"
                , MysqlAdapter::PHINX_TYPE_DATETIME
                , [
                    "null"      => false
                    , "default" => "CURRENT_TIMESTAMP"
                    , "after"   => "expire_ts"
                ]
            )
            ->save();

    }

}
