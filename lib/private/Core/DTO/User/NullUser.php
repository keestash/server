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

namespace Keestash\Core\DTO\User;

use DateTimeImmutable;
use doganoo\PHPAlgorithms\Datastructure\Table\HashTable;

final class NullUser extends User {

    public function __construct() {
        $this->setId(-1);
        $this->setName('nullUser');
        $this->setPassword('nullPassword');
        $this->setCreateTs(new DateTimeImmutable());
        $this->setFirstName('Null');
        $this->setLastName('User');
        $this->setEmail('null@keestash.com');
        $this->setPhone('0123456789');
        $this->setWebsite('keestash.com');
        $this->setHash(md5('nullUser'));
        $this->setLocked(true);
        $this->setDeleted(true);
        $this->setJWT(null);
        $this->setLocale('de');
        $this->setLanguage('DE');
        $this->setRoles(new HashTable());
    }

}