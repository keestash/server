<?php
declare(strict_types=1);

use Keestash\Core\Repository\Migration\Base\KeestashMigration;
use KSP\Core\DTO\Queue\IMessage;

final class V20220919182840 extends KeestashMigration
{
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
    public function change(): void
    {
        $this->table('queue')
            ->changeColumn(
                'type'
                , KeestashMigration::ENUM
                , [
                    "length"    => "100"
                    , "null"    => false
                    , "default" => IMessage::TYPE_EMAIL
                    , 'values'  => [IMessage::TYPE_EMAIL, IMessage::TYPE_EVENT]
                    , "after"   => 'payload'
                ]
            )
            ->save();
    }
}
