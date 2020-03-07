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

namespace KSA\Account\Api;

use doganoo\SimpleRBAC\Test\DataProvider\Context;
use Keestash\Api\AbstractApi;
use Keestash\Api\Response\DefaultResponse;
use Keestash\Core\DTO\HTTP;
use Keestash\Core\DTO\User;
use KSA\Account\Application\Application;
use KSP\Api\IResponse;
use KSP\Core\DTO\IToken;
use KSP\Core\DTO\IUser;
use KSP\Core\Permission\IPermission;
use KSP\Core\Repository\Permission\IPermissionRepository;
use KSP\Core\Repository\User\IUserRepository;
use KSP\L10N\IL10N;

class UpdateUserData extends AbstractApi {

    private $userService = null;
    private $userManager = null;
    /** @var IUser $user */
    private $user              = null;
    private $response          = null;
    private $l10n              = null;
    private $permissionManager = null;

    public function __construct(
        User $userService
        , IUserRepository $userManager
        , IL10N $l10n
        , IPermissionRepository $permissionManager
        , ?IToken $token = null
    ) {
        $this->userService       = $userService;
        $this->userManager       = $userManager;
        $this->l10n              = $l10n;
        $this->permissionManager = $permissionManager;

        parent::__construct($l10n, $token);
    }

    public function onCreate(array $parameters): void {
        /** @var User|null $user */
        $user = $this->userManager->getUserById($parameters["user_id"] ?? "");
        if (null === $user) {
            $this->prepareResponse(
                IResponse::RESPONSE_CODE_NOT_OK
                , "No user found"
            );
            return;
        }
        $user->setFirstName($parameters["first_name"] ?? "");
        $user->setLastName($parameters["last_name"] ?? "");
        $user->setEmail($parameters["email"] ?? "");
        $user->setPhone($params["phone"] ?? "");
        $user->setWebsite($parameters["website"] ?? "");
        $this->user = $user;

        $this->preparePermission(
            $user
        );
    }

    private function prepareResponse(int $code, string $message): IResponse {
        $response = new DefaultResponse();
        $response->setCode(HTTP::OK);
        $response->addMessage(
            $code
            ,
            [
                "message" => $this->l10n->translate($message)
            ]
        );
        return $response;
    }

    private function preparePermission(IUser $contextUser): IPermission {
        /** @var IPermission $permission */
        $permission = $this->permissionManager->getPermission(Application::PERMISSION_UPDATE_PROFILE_IMAGE);
        $context    = new Context();
        $context->addUser($contextUser);
        $permission->setContext($context);
        return $permission;
    }

    public function create(): void {
        if (null === $this->user) return;
        if (false === $this->valid()) return;
        $updated = $this->userManager->update($this->user);
        if (false === $updated) return;

        parent::setResponse(
            $this->prepareResponse(
                IResponse::RESPONSE_CODE_OK
                , "User Updated!"
            )
        );

    }

    private function valid(): bool {
        if ("" === trim($this->user->getFirstName())) return false;
        if ("" === trim($this->user->getLastName())) return false;
        if (false === $this->userService->validEmail($this->user->getEmail())) return false;
        if ("" === trim($this->user->getPhone())) return false;
        if ("" === trim($this->user->getWebsite())) return false;
        return true;
    }

    public function afterCreate(): void {

    }

    public function getResponse(): IResponse {
        return $this->response;
    }

}