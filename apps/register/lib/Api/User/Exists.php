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

namespace KSA\Register\Api\User;

use Keestash;
use Keestash\Api\AbstractApi;

use KSP\Api\IResponse;
use KSP\Core\DTO\User\IUser;

class Exists extends AbstractApi {

    public function onCreate(array $parameters): void {

    }

    public function create(): void {
        $userName = $this->getParameter("username", null);
        $users    = Keestash::getServer()->getUsersFromCache();
        $user     = null;

        /** @var IUser $iUser */
        foreach ($users as $iUser) {

            if (strtolower($userName) === strtolower($iUser->getName())) {
                $user = $iUser;
                break;
            }

        }

        $this->createAndSetResponse(
            IResponse::RESPONSE_CODE_OK
            , [
                "user_exists" => $user !== null
            ]
        );

    }

    public function afterCreate(): void {

    }

}
