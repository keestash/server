<?php
declare(strict_types=1);

use Keestash\Core\Repository\Migration\Base\KeestashMigration;

final class AdditionalProperties extends KeestashMigration {

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
        $this->table(
            "pwm_additional_data"
            , [
                'id'            => false
                , 'primary_key' => [
                    'id',
                ]
            ]
        )
            ->addColumn(
                "id"
                , KeestashMigration::STRING
                , [
                    "null"      => false
                    , "comment" => "The data id"
                ]
            )
            ->addColumn(
                "key"
                , KeestashMigration::STRING
                , [
                    "null"      => false
                    , "comment" => "The Key"
                    , "after"   => "id"
                ]
            )
            ->addColumn(
                "value"
                , KeestashMigration::BLOB
                , [
                    "null"      => false
                    , "comment" => "The comment"
                    , "after"   => "key"
                ]
            )
            ->addColumn(
                "node_id"
                , "integer"
                , [
                    "null"      => false
                    , "comment" => "The node getting commented"
                    , "after"   => "value"
                    , 'signed' => false
                ]
            )
            ->addColumn(
                "create_ts"
                , "datetime"
                , [
                    "null"      => false
                    , "comment" => "The create ts"
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
            ->save();
    }

}
