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

use doganoo\PHPUtil\Datatype\StringClass;
use doganoo\SimpleRBAC\Test\DataProvider\Context;
use Keestash;
use Keestash\Api\AbstractApi;
use Keestash\Api\Response\DefaultResponse;
use Keestash\Core\DTO\HTTP;
use Keestash\Core\Service\User\UserService;
use KSA\Account\Application\Application;
use KSP\Api\IResponse;
use KSP\Core\DTO\IToken;
use KSP\Core\DTO\User\IUser;
use KSP\Core\Permission\IPermission;
use KSP\Core\Repository\Permission\IPermissionRepository;
use KSP\Core\Repository\User\IUserRepository;
use KSP\L10N\IL10N;

class UpdatePassword extends AbstractApi {

    private $currentPassword      = null;
    private $newPassword          = null;
    private $newPasswordRepeat    = null;
    private $userId               = null;
    private $operationSuccessfull = false;

    /** @var IUser $currentUser */
    private $currentUser = null;
    /** @var IUser $oldUser */
    private $oldUser = null;

    private $userManager       = null;
    private $userService       = null;
    private $permissionManager = null;

    public function __construct(
        IUserRepository $userManager
        , UserService $userService
        , IL10N $l10n
        , IPermissionRepository $permissionManager
        , ?IToken $token = null
    ) {
        parent::__construct($l10n, $token);

        $this->userManager       = $userManager;
        $this->userService       = $userService;
        $this->permissionManager = $permissionManager;
    }

    public function onCreate(array $parameters): void {

        $this->userId            = $parameters["user_id"];
        $this->currentPassword   = $parameters["current_password"];
        $this->newPassword       = $parameters["password"];
        $this->newPasswordRepeat = $parameters["password_repeat"];
        $user                    = $this->userManager->getUserById((string) $this->userId);
        $this->currentUser       = $user;
        // mission critical!!
        // otherwise, the olduser variable will
        // hold the new password!!
        $this->oldUser = clone $user;

        $permission = $this->preparePermission($user);
        parent::setPermission($permission);

    }

    private function preparePermission(IUser $contextUser): IPermission {
        /** @var IPermission $permission */
        $permission = $this->permissionManager->getPermission(Application::PERMISSION_UPDATE_PASSWORD);
        $context    = new Context();
        $context->addUser($contextUser);
        $permission->setContext($context);
        return $permission;
    }

    public function create(): void {
        if (null === $this->currentUser) return;
        if (false === (new StringClass($this->newPassword))->equals($this->newPasswordRepeat)) return;
        if (false === $this->userService->validatePassword((string) $this->currentPassword, $this->currentUser->getPassword())) return;
        if (false === $this->userService->passwordHasMinimumRequirements((string) $this->newPassword)) return;

        $hash = $this->userService->hashPassword((string) $this->newPassword);
        $this->currentUser->setPassword($hash);

        Keestash::getServer()
            ->getPasswordChangedHookManager()
            ->executePre();

        $updated = $this->userManager->update($this->currentUser);

        if (true === $updated) {
            $this->operationSuccessfull = true;
            $response                   = new DefaultResponse();
            $response->setCode(HTTP::OK);
            $response->addMessage(IResponse::RESPONSE_CODE_OK,
                [
                    "password updated"
                ]
            );

            parent::setResponse($response);
        }
    }

    public function afterCreate(): void {
        if (false === $this->operationSuccessfull) return;

        Keestash::getServer()
            ->getPasswordChangedHookManager()
            ->executePost(
                $this->currentUser
                , $this->oldUser
            );

    }

}
