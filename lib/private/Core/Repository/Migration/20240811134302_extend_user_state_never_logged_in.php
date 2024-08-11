<?php
declare(strict_types=1);

use Keestash\Core\DTO\User\UserStateName;
use Keestash\Core\Repository\Migration\Base\KeestashMigration;

final class ExtendUserStateNeverLoggedIn extends KeestashMigration {

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
                        UserStateName::DELETE->value,
                        UserStateName::LOCK->value,
                        UserStateName::REQUEST_PW_CHANGE->value,
                        UserStateName::LOCK_CANDIDATE_STAGE_ONE->value,
                        UserStateName::LOCK_CANDIDATE_STAGE_TWO->value,
                        UserStateName::NEVER_LOGGED_IN->value,
                    ]
                    , "after"                               => "user_id"
                ]
            )
            ->save();
    }

}
