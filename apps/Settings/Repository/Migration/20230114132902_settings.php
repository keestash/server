<?php
declare(strict_types=1);

use Keestash\Core\Repository\Migration\Base\KeestashMigration;

final class Settings extends KeestashMigration {

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
        $this->table("setting"
            ,
            [
                'id'            => false
                , 'primary_key' => ['key']
            ]
        )
            ->addColumn(
                "key"
                , KeestashMigration::STRING
                , [
                    "length"    => "100"
                    , "comment" => "The setting name"
                    , "null"    => false
                ]
            )
            ->addColumn(
                "value"
                , KeestashMigration::TEXT
                , [
                    "comment" => "The value"
                    , "null"  => false
                    , "after" => "id"
                ]
            )
            ->addColumn(
                "create_ts"
                , KeestashMigration::DATETIME
                , [
                    "comment"   => "The setting's creation time as unix timestamp"
                    , "null"    => false
                    , "after"   => "value"
                    , "default" => "CURRENT_TIMESTAMP"
                ]
            )
            ->save();
    }

}
