<?php
declare(strict_types=1);

namespace KSP\Core\Service\User;

use KSP\Core\DTO\User\IUser;
use KSP\Core\DTO\User\IUserState;

interface IUserStateService {

    public const array STATE_HIERARCHY = [
        0 => IUserState::USER_STATE_LOCK_CANDIDATE_STAGE_ONE,
        1 => IUserState::USER_STATE_LOCK_CANDIDATE_STAGE_TWO,
        2 => IUserState::USER_STATE_LOCK,
        3 => IUserState::USER_STATE_DELETE
    ];

    public const array STATE_HIERARCHY_REVERTED = [
        IUserState::USER_STATE_LOCK_CANDIDATE_STAGE_ONE => 0,
        IUserState::USER_STATE_LOCK_CANDIDATE_STAGE_TWO => 1,
        IUserState::USER_STATE_LOCK                     => 2,
        IUserState::USER_STATE_DELETE                   => 3
    ];

    public function getState(IUser $user): IUserState;

    public function setState(IUserState $userState): void;

    public function forceLock(IUser $user): void;

    public function forceDelete(IUser $user): void;

    public function clear(IUser $user): void;

    public function clearCarefully(IUser $user, string $expectedState): void;

}
