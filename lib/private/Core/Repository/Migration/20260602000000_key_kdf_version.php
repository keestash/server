<?php

use Keestash\Core\Repository\Migration\Base\KeestashMigration;

class KeyKdfVersion extends KeestashMigration {

    public function up(): void {
        $this->table('key')
            ->addColumn(
                'kdf_version'
                , KeestashMigration::STRING
                , [
                    'null'   => false
                    , 'limit' => 32
                    , 'after' => 'value'
                ]
            )
            ->save();

        $this->execute(
            "UPDATE `key` SET `kdf_version` = 'scrypt-aes-gcm-v1'"
        );
    }

    public function down(): void {
        $this->table('key')
            ->removeColumn('kdf_version')
            ->save();
    }

}
