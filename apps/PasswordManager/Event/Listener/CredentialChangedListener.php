<?php
declare(strict_types=1);
/**
 * Keestash
 *
 * Copyright (C) <2022> <Dogan Ucar>
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

use KSA\PasswordManager\Event\Node\Credential\CredentialChangedEvent;
use KSA\PasswordManager\Repository\Node\PwnedBreachesRepository;
use KSA\PasswordManager\Repository\Node\PwnedPasswordsRepository;
use KSP\Core\Manager\EventManager\IListener;
use Symfony\Contracts\EventDispatcher\Event;

class CredentialChangedListener implements IListener {

    private PwnedPasswordsRepository $pwnedPasswordsRepository;
    private PwnedBreachesRepository  $pwnedBreachesRepository;

    public function __construct(
        PwnedPasswordsRepository  $pwnedPasswordsRepository
        , PwnedBreachesRepository $pwnedBreachesRepository
    ) {
        $this->pwnedPasswordsRepository = $pwnedPasswordsRepository;
        $this->pwnedBreachesRepository  = $pwnedBreachesRepository;
    }

    public function execute(Event $event): void {
        if (!($event instanceof CredentialChangedEvent)) {
            return;
        }

        $this->pwnedPasswordsRepository->replace($event->getPasswords());
        $this->pwnedBreachesRepository->replace($event->getBreaches());
    }

}