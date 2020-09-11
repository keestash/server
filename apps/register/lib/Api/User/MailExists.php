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

use doganoo\PHPUtil\Log\FileLogger;
use Exception;
use Keestash;
use Keestash\Api\AbstractApi;
use Keestash\Core\Permission\PermissionFactory;
use KSP\Api\IResponse;
use KSP\Core\DTO\User\IUser;

class MailExists extends AbstractApi {

    public function onCreate(array $parameters): void {
        $this->setPermission(
            PermissionFactory::getDefaultPermission()
        );
    }

    public function create(): void {
        $emailAddress = $this->getParameter("address", null);
        $users        = Keestash::getServer()->getUsersFromCache();
        $user         = null;

        try {

            /** @var IUser $iUser */
            foreach ($users as $iUser) {

                if (strtolower($emailAddress) === strtolower($iUser->getEmail())) {
                    $user = $iUser;
                    break;
                }

            }

        } catch (Exception $exception) {
            FileLogger::error($exception->getTraceAsString());
        }

       $this->createAndSetResponse(
            IResponse::RESPONSE_CODE_OK
            , [
                "email_address_exists" => $user !== null
            ]
        );

    }

    public function afterCreate(): void {

    }

}
