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

namespace KSA\PasswordManager\Event\Node\Credential;

use DateTimeImmutable;
use DateTimeInterface;
use Keestash\Core\DTO\Event\Event;
use KSA\PasswordManager\Entity\Node\Credential\Credential;
use KSA\PasswordManager\Entity\Node\Pwned\Breaches;
use KSA\PasswordManager\Entity\Node\Pwned\Passwords;

class CredentialChangedEvent extends Event {

    private Passwords $passwords;
    private Breaches  $breaches;

    public function __construct(
        Credential           $credential
        , ?DateTimeInterface $updateTs = null
    ) {

        $this->passwords = new Passwords(
            $credential
            , 0
            , new DateTimeImmutable()
            , $updateTs
        );

        $this->breaches = new Breaches(
            $credential
            , null
            , new DateTimeImmutable()
            , $updateTs
        );
    }

    public function getPasswords(): Passwords {
        return $this->passwords;
    }

    public function getBreaches(): Breaches {
        return $this->breaches;
    }

    public function jsonSerialize(): array {
        return [
            'passwords'  => $this->getPasswords()
            , 'breaches' => $this->getBreaches()
        ];
    }

}