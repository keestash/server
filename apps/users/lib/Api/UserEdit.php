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

namespace KSA\Users\Api;

use Keestash\Api\AbstractApi;
use Keestash\Core\DTO\User;
use Keestash\Core\Permission\PermissionFactory;
use KSA\Users\Exception\NoUpdateTypeGivenException;
use KSP\Api\IResponse;
use KSP\Core\DTO\IToken;
use KSP\Core\Repository\User\IUserRepository;
use KSP\L10N\IL10N;

class UserEdit extends AbstractApi {

    /** @var array $parameters */
    private $parameters;

    /** @var IUserRepository $userRepository */
    private $userRepository;

    public function __construct(
        IL10N $l10n
        , IUserRepository $userRepository
        , ?IToken $token = null
    ) {
        parent::__construct($l10n, $token);

        $this->userRepository = $userRepository;
    }

    public function onCreate(array $parameters): void {

        $this->parameters = $parameters;

        $this->setPermission(
            PermissionFactory::getDefaultPermission()
        );

    }

    public function create(): void {
        $type   = $this->getParameter("type", null);
        $value  = $this->getParameter("value", null);
        $userId = $this->getParameter("user_id", null);

        if (
            true === $this->isEmpty($type)
            || true === $this->isEmpty($value)
            || true === $this->isEmpty($userId)
        ) {

            $this->createAndSetResponse(
                IResponse::RESPONSE_CODE_NOT_OK
                , [
                    "message" => $this->getL10N()->translate("no parameters given")
                ]
            );

            return;
        }

        /** @var User $user */
        $user = $this->userRepository->getUserById((string) $userId);

        if (null === $user) {

            $this->createAndSetResponse(
                IResponse::RESPONSE_CODE_NOT_OK
                , [
                    "message" => $this->getL10N()->translate("no user found")
                ]
            );

            return;
        }

        // TODO validate input!

        switch ($type) {
            case "firstname":
                $user->setFirstName($value);
                break;
            case "lastname":
                $user->setLastName($value);
                break;
            case "password":
                $user->setPassword($value);
                break;
            case "email":
                $user->setEmail($value);
                break;
            case "phone":
                $user->setPhone($value);
                break;
            case "website":
                $user->setWebsite($value);
                break;
            default:
                throw new NoUpdateTypeGivenException();
        }

        $updated = $this->userRepository->update($user);

        if (false === $updated) {
            $this->createAndSetResponse(
                IResponse::RESPONSE_CODE_NOT_OK
                , [
                    "message" => $this->getL10N()->translate("user could not be found")
                ]
            );

            return;
        }

        $this->createAndSetResponse(
            IResponse::RESPONSE_CODE_OK
            , [
                "message" => $this->getL10N()->translate("user updated")
            ]
        );
    }

    private function isEmpty($val): bool {
        return null === $val || "" === trim($val);
    }

    public function afterCreate(): void {
        // TODO: Implement afterCreate() method.
    }

}