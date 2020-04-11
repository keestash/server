<?php

use Keestash\Core\Repository\Migration\Base\KeestashMigration;
use KSA\Users\Object\User\DeleteCandidate;
use KSP\Core\DTO\User\IUserState;

class UserState extends KeestashMigration {

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
            ->addColumn(
                "user_id"
                , KeestashMigration::INTEGER
                , [
                    "null"      => false
                    , "comment" => "the user which's state is configured"
                ]
            )
            ->addColumn(
                "state"
                , KeestashMigration::ENUM
                , [
                    "null"                                  => false
                    , "comment"                             => "The user's state"
                    , KeestashMigration::OPTION_NAME_VALUES => [
                        IUserState::USER_STATE_DELETE
                        , IUserState::USER_STATE_LOCK
                    ]
                    , "after"                               => "user_id"
                ]
            )
            ->addColumn(
                "valid_from"
                , KeestashMigration::DATETIME
                , [
                    "null"      => false
                    , "comment" => "The records valid from date"
                    , "after"   => "state"
                ]
            )
            ->addColumn(
                "create_ts"
                , KeestashMigration::DATETIME
                , [
                    "null"      => false
                    , "comment" => "The records create ts"
                    , "after"   => "valid_from"
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
