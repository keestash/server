<?php
declare(strict_types=1);

use Keestash\Core\Repository\Migration\Base\KeestashMigration;

final class MailLog extends KeestashMigration {

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
        $this->table("mail_log", ['id' => false, 'primary_key' => ['id']])
            ->addColumn(
                "id"
                , KeestashMigration::STRING
                , [
                    "length"    => "100"
                    , "comment" => "The logs's id"
                    , "null"    => false
                ]
            )
            ->addColumn(
                'subject'
                , KeestashMigration::STRING
                , [
                    "length"    => "100"
                    , "comment" => "The summary of the mail"
                    , "null"    => false
                ]
            )
            ->addColumn(
                'create_ts'
                , KeestashMigration::DATETIME
                , [
                    "comment"   => "The send date"
                    , "null"    => false
                    , "default" => "CURRENT_TIMESTAMP"
                ]
            )
            ->save();
    }

}
