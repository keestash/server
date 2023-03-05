<?php
declare(strict_types=1);

use Keestash\Core\Repository\Migration\Base\KeestashMigration;

final class Derivation extends KeestashMigration {

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
        $this->table("derivation", ['id' => false, 'primary_key' => ['id']])
            ->addColumn(
                "id"
                , KeestashMigration::STRING
                , [
                    "length"    => "100"
                    , "comment" => "The key derivation id"
                    , "null"    => false
                ]
            )
            ->addColumn(
                "user_id"
                , KeestashMigration::INTEGER
                , [
                    "null"      => false
                    , "comment" => "the user"
                    , "after"   => "id"
                ]
            )
            ->addColumn(
                'derivation'
                , KeestashMigration::BLOB
                , [
                    "comment" => "The derived key"
                    , "null"  => false
                    , "after" => 'user_id'
                ]
            )
            ->addColumn(
                'create_ts'
                , KeestashMigration::DATETIME
                , [
                    "comment"   => "The send date"
                    , "null"    => false
                    , "default" => "CURRENT_TIMESTAMP"
                    , "after"   => "derivation"
                ]
            )
            ->addForeignKey(
                'user_id'
                , 'user'
                , [
                    'id'
                ]
                , [
                    'delete'   => 'CASCADE'
                    , 'update' => 'CASCADE'
                ]
            )
            ->save();
    }

}
