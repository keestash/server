<?php
declare(strict_types=1);

use Keestash\Core\Repository\Migration\Base\KeestashMigration;

final class ExtendPublicShareByPassword extends KeestashMigration {

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
        $this->table("pwm_public_share")
            ->addColumn(
                "password"
                , "string"
                , [
                    "length"    => "500"
                    , "comment" => "The password to protect the share"
                    , "null"    => false
                    , "after"   => "hash"
                ]
            )
            ->save();
    }

}
