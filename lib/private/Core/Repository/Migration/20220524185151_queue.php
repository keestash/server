<?php
declare(strict_types=1);

use Keestash\Core\Repository\Migration\Base\KeestashMigration;
use KSP\Core\DTO\Queue\IMessage;

final class Queue extends KeestashMigration {

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
        $this->table("queue", ['id' => false, 'primary_key' => ['id']])
            ->addColumn(
                "id"
                , KeestashMigration::STRING
                , [
                    "length"    => "100"
                    , "comment" => "The message's id"
                    , "null"    => false
                ]
            )
            ->addColumn(
                "priority"
                , KeestashMigration::INTEGER
                , [
                    "comment"   => "The message's priority"
                    , "null"    => false
                    , "after"   => "id"
                    , "default" => 1
                ]
            )
            ->addColumn(
                "attempts"
                , KeestashMigration::INTEGER
                , [
                    "comment"   => "The number of attempts the message has been tried to process"
                    , "null"    => false
                    , "after"   => "priority"
                    , "default" => 0
                ]
            )
            ->addColumn(
                "payload"
                , KeestashMigration::BLOB
                , [
                    "comment"   => "The message payload"
                    , "null"    => false
                    , "after"   => "attempts"
                    , "default" => ''
                ]
            )
            ->addColumn(
                "stamps"
                , KeestashMigration::BLOB
                , [
                    "comment"   => "The message stamps"
                    , "null"    => false
                    , "after"   => "attempts"
                    , "default" => ''
                ]
            )
            ->addColumn(
                "reserved_ts"
                , KeestashMigration::DATETIME
                , [
                    "comment"   => "when the message should be processed"
                    , "null"    => false
                    , "after"   => "type"
                    , "default" => "CURRENT_TIMESTAMP"
                ]
            )
            ->addColumn(
                "create_ts"
                , KeestashMigration::DATETIME
                , [
                    "comment"   => "The message's creation time as unix timestamp"
                    , "null"    => false
                    , "after"   => "reserved_ts"
                    , "default" => "CURRENT_TIMESTAMP"
                ]
            )
            ->save();

    }

}
