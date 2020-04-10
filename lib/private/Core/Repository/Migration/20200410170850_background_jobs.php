<?php

use doganoo\Backgrounder\BackgroundJob\Job;
use Keestash\Core\Repository\Migration\Base\KeestashMigration;
use Phinx\Db\Adapter\MysqlAdapter;

class BackgroundJobs extends KeestashMigration {

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

        $this->table("background_job")
            ->addColumn(
                "name"
                , MysqlAdapter::PHINX_TYPE_STRING
                , [
                "null"      => false
                , "comment" => "The job's name"
            ])
            ->addColumn(
                "interval"
                , MysqlAdapter::PHINX_TYPE_INTEGER
                , [
                "null"      => false
                , "comment" => "The job's interval to run"
                , "after"   => "name"
            ])
            ->addColumn(
                "type"
                , MysqlAdapter::PHINX_TYPE_ENUM
                , [
                "null"      => false
                , "comment" => "The job's interval type"
                , "values"  => [
                    Job::JOB_TYPE_ONE_TIME
                    , Job::JOB_TYPE_REGULAR
                ]
                , "default" => Job::JOB_TYPE_REGULAR
                , "after"   => "interval"
            ])
            ->addColumn(
                "last_run"
                , MysqlAdapter::PHINX_TYPE_DATETIME
                , [
                "null"      => true
                , "comment" => "The job's last run timestamp"
                , "default" => null
                , "after"   => "type"
            ])
            ->addColumn(
                "info"
                , MysqlAdapter::PHINX_TYPE_JSON
                , [
                "null"      => true
                , "comment" => "additional info"
                , "default" => null
                , "after"   => "last_run"
            ])
            ->addColumn(
                "create_ts"
                , MysqlAdapter::PHINX_TYPE_DATETIME
                , [
                "null"      => false
                , "comment" => "The job's create ts"
                , "default" => "CURRENT_TIMESTAMP"
                , "after"   => "info"
            ])
            ->save();

    }

}
