<?php
declare(strict_types=1);

use Keestash\Core\Repository\Migration\Base\KeestashMigration;

final class PaymentLog extends KeestashMigration {

    /**
     * Change Method.
     *
     * Creates the payment_log table used to persist Mollie webhook payloads and
     * checkout sessions. The `key` column holds the payment session id (or the
     * Mollie payment id as a fallback) so a webhook can be correlated to the
     * registration that started it. There is deliberately no foreign key to the
     * user, because a payment may be logged before the user is activated.
     */
    public function change(): void {
        $this->table('payment_log')
            ->addColumn(
                'key'
                , KeestashMigration::STRING
                , [
                    'length'    => 255
                    , 'comment' => 'The payment session id used to correlate the log'
                    , 'null'    => false
                ]
            )
            ->addColumn(
                'log'
                , KeestashMigration::TEXT
                , [
                    'comment' => 'The JSON encoded payment payload'
                    , 'null'  => false
                ]
            )
            ->addColumn(
                'create_ts'
                , KeestashMigration::DATETIME
                , [
                    'comment'   => 'The creation date'
                    , 'null'    => false
                    , 'default' => 'CURRENT_TIMESTAMP'
                ]
            )
            ->addIndex(['key'])
            ->save();
    }

}
