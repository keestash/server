<?php
declare(strict_types=1);

namespace KSP\Core\Service\User;

use Keestash\Core\DTO\User\UserStateName;
use KSP\Core\DTO\User\IUser;
use KSP\Core\DTO\User\IUserState;

interface IUserStateService {

    public function getState(IUser $user): IUserState;

    public function setState(IUserState $userState): void;

    public function forceLock(IUser $user): void;

    public function forceDelete(IUser $user): void;

    public function clear(IUser $user): void;

    public function clearCarefully(IUser $user, UserStateName $expectedState): void;

}
