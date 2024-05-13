<?php
declare(strict_types=1);

namespace Keestash\Core\DTO\User;

use DateTimeInterface;
use Keestash\Exception\KeestashException;
use KSP\Core\DTO\User\IUser;
use KSP\Core\DTO\User\IUserState;

class NullUserState implements IUserState {

    #[\Override] public function getId(): int {
        return PHP_INT_MIN;
    }

    #[\Override] public function getUser(): IUser {
        throw new KeestashException();
    }

    #[\Override] public function getState(): UserStateName {
        return UserStateName::NULL;
    }

    #[\Override] public function getValidFrom(): DateTimeInterface {
        return (new \DateTimeImmutable())->setTimestamp(0);
    }

    #[\Override] public function getStateHash(): ?string {
        return null;
    }

    #[\Override] public function getCreateTs(): DateTimeInterface {
        return (new \DateTimeImmutable())->setTimestamp(0);
    }

}
