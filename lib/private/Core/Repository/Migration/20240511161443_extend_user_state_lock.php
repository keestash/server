<?php
declare(strict_types=1);

use Keestash\Core\Repository\Migration\Base\KeestashMigration;
use KSP\Core\DTO\User\IUserState;

final class ExtendUserStateLock extends KeestashMigration {

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
                        , IUserState::USER_STATE_LOCK_CANDIDATE_STAGE_ONE
                        , IUserState::USER_STATE_LOCK_CANDIDATE_STAGE_TWO
                    ]
                    , "after"                               => "user_id"
                ]
            )
            ->save();
    }

}
