<?php
declare(strict_types=1);

use Keestash\Core\Repository\Migration\Base\KeestashMigration;

final class UserSettings extends KeestashMigration {

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
        $this->table("user_setting"
            ,
            [
                'id'            => false
                , 'primary_key' => ['key', 'user_id']
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
                "user_id"
                , KeestashMigration::INTEGER
                , [
                    "null"      => false
                    , "comment" => "The user's id"
                    , "after" => "key"
                ]
            )
            ->addColumn(
                "value"
                , KeestashMigration::TEXT
                , [
                    "comment" => "The value"
                    , "null"  => false
                    , "after" => "user_id"
                ]
            )
            ->addColumn(
                "create_ts"
                , KeestashMigration::DATETIME
                , [
                    "comment"   => "The setting's create date"
                    , "null"    => false
                    , "after"   => "value"
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
