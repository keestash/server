<?php
declare(strict_types=1);
/**
 * Keestash
 *
 * Copyright (C) <2020> <Dogan Ucar>
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

namespace Keestash\Core\Service\Validation\Rule;

use Keestash;
use KSP\Core\Permission\IUser;
use Laminas\Validator\AbstractValidator;

class Username extends AbstractValidator {

    private const USERNAME = "username";

    protected array $messageTemplates = [
        Username::USERNAME => "%username% already exists"
    ];

    public function isValid($value) {
        $this->setValue($value);

        $users = Keestash::getServer()->getUsersFromCache();

        /** @var IUser $iUser */
        foreach ($users as $iUser) {
            if (strtolower($iUser->getName()) === strtolower($value)) {
                $this->error(Username::USERNAME);
                return false;
            }
        }

        return false;

    }

}
