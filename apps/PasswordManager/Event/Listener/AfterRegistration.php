<?php
declare(strict_types=1);
/**
 * Keestash
 *
 * Copyright (C) <2019> <Dogan Ucar>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published
 * by the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace KSA\PasswordManager\Event\Listener;

use doganoo\PHPAlgorithms\Common\Exception\InvalidKeyTypeException;
use doganoo\PHPAlgorithms\Common\Exception\UnsupportedKeyTypeException;
use Keestash\Core\Service\User\Event\UserCreatedEvent;
use Keestash\Exception\KeestashException;
use KSA\PasswordManager\Event\Listener\AfterRegistration\CreateKey;
use KSA\PasswordManager\Event\Listener\AfterRegistration\CreateStarterPassword;
use KSA\PasswordManager\Exception\DefaultPropertiesNotSetException;
use KSA\PasswordManager\Exception\KeyNotCreatedException;
use KSA\PasswordManager\Exception\KeyNotStoredException;
use KSP\Core\DTO\User\IUser;
use KSP\Core\Manager\EventManager\IListener;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Class AfterRegistration
 *
 * @package KSA\PasswordManager\Hook
 * @author  Dogan Ucar <dogan@dogan-ucar.de>
 *
 */
class AfterRegistration implements IListener {

    private CreateKey             $createKey;
    private CreateStarterPassword $createStarterPassword;

    public function __construct(
        CreateKey $createKey
        , CreateStarterPassword $createStarterPassword
    ) {
        $this->createKey             = $createKey;
        $this->createStarterPassword = $createStarterPassword;
    }

    /**
     * @param UserCreatedEvent $event
     * @throws DefaultPropertiesNotSetException
     * @throws KeyNotCreatedException
     * @throws KeyNotStoredException
     * @throws KeestashException
     * @throws InvalidKeyTypeException
     * @throws UnsupportedKeyTypeException
     */
    public function execute(Event $event): void {

        // base case: we do not create stuff for the system user
        if ($event->getUser()->getId() === IUser::SYSTEM_USER_ID) {
            return;
        }

        $this->createKey->run($event->getUser());
        $this->createStarterPassword->run($event->getUser());

    }

}
