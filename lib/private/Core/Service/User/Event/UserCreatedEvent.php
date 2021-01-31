<?php
declare(strict_types=1);

namespace Keestash\Core\Service\User\Event;

use KSP\Core\DTO\User\IUser;
use Symfony\Contracts\EventDispatcher\Event;

class UserCreatedEvent extends Event {

    private IUser $user;

    public function __construct(IUser $user) {
        $this->user = $user;
    }

    /**
     * @return IUser
     */
    public function getUser(): IUser {
        return $this->user;
    }

}