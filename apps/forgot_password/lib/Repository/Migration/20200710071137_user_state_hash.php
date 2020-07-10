<?php

use Keestash\Core\Repository\Migration\Base\KeestashMigration;
use KSP\Core\DTO\User\IUserState;

class UserStateHash extends KeestashMigration {

    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-abstractmigration-class
     *
     * The following commands can be used in this method and Phinx will
     * automatically reverse them when rolling back:
     *
     *    createTable
     *    renameTable
     *    addColumn
     *    addCustomColumn
     *    renameColumn
     *    addIndex
     *    addForeignKey
     *
     * Any other destructive changes will result in an error when trying to
     * rollback the migration.
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function change() {
        $this->table("user_state")
            ->changeColumn(
                "state"
                , KeestashMigration::ENUM
                , [
                    "null"                                  => false
                    , "comment"                             => "The user's state"
                    , KeestashMigration::OPTION_NAME_VALUES => [
                        IUserState::USER_STATE_DELETE
                        , IUserState::USER_STATE_LOCK
                        , IUserState::USER_STATE_REQUEST_PW_CHANGE
                    ]
                    , "after"                               => "user_id"
                ]
            )
            ->addColumn(
                "state_hash"
                , KeestashMigration::STRING
                , [
                    "null"      => true
                    , "comment" => "The hash identifying the user state"
                    , "after"   => "state"
                    , "default" => null
                ]
            )
            ->save();
    }

}
