<?php
declare(strict_types=1);

use Keestash\Core\Repository\Migration\Base\KeestashMigration;
use Phinx\Db\Adapter\AdapterInterface;

final class RefactorApiLog extends KeestashMigration {

    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-change-method
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function change(): void {
        $this->table("apilog")
            ->drop()
            ->save();

        $this->table(
            "apilog",
            [
                'id'            => false
                , 'primary_key' => [
                'id',
            ]
            ]
        )
            ->addColumn(
                "id",
                AdapterInterface::PHINX_TYPE_STRING,
                [
                    "length" => 100,
                    "null"   => false,
                ]
            )
            ->addColumn(
                "request_id",
                AdapterInterface::PHINX_TYPE_STRING,
                [
                    "length" => 100,
                    "null"   => false,
                ]
            )
            ->addColumn(
                "data",
                AdapterInterface::PHINX_TYPE_JSON,
                [
                    "null" => false,
                ]
            )
            ->addColumn(
                "start"
                , KeestashMigration::DATETIME
                , [
                    "null" => false
                ]
            )
            ->addColumn(
                "end"
                , KeestashMigration::DATETIME
                , [
                    "null" => false
                ]
            )
            ->addColumn(
                "create_ts"
                , KeestashMigration::DATETIME
                , [
                    "null" => false
                ]
            )
            ->addIndex(['request_id'])
            ->save();
    }

}
