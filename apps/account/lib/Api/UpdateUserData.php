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

use Keestash\Api\AbstractApi;
use Keestash\Core\Service\User\UserService;
use KSP\Core\DTO\IToken;
use KSP\Core\DTO\IUser;
use KSP\Core\Repository\Permission\IPermissionRepository;
use KSP\Core\Repository\User\IUserRepository;
use KSP\L10N\IL10N;

class UpdateUserData extends AbstractApi {

    private $userService = null;
    private $userManager = null;
    /** @var IUser $user */
    private $user              = null;
    private $l10n              = null;
    private $permissionManager = null;

    public function __construct(
        UserService $userService
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
<<<<<<< Updated upstream
        /** @var User|null $user */
        $user = $this->userManager->getUserById($parameters["user_id"] ?? "");
=======
        $this->setPermission(
            PermissionFactory::getDefaultPermission()
        );
        $userId    = $parameters["user_id"] ?? null;
        $firstName = $parameters["first_name"] ?? "";
        $lastName  = $parameters["last_name"] ?? "";
        $email     = $parameters["email"] ?? "";
        $phone     = $parameters["phone_number"] ?? "";
        $website   = $parameters["website"] ?? "";

        if (true === Util::isEmpty($userId)) {
            $this->createAndSetResponse(
                IResponse::RESPONSE_CODE_NOT_OK
                , [
                    "message" => $this->getL10N()->translate("User not found")
                ]
            );
            return;
        }

        /** @var User $user */
        $user = $this->userManager->getUserById((string) $userId);

>>>>>>> Stashed changes
        if (null === $user) {
            $this->createAndSetResponse(
                IResponse::RESPONSE_CODE_NOT_OK
                , [
                    "message" => $this->getL10N()->translate("no user found")
                ]
            );
            return;
        }

        $user->setFirstName($firstName);
        $user->setLastName($lastName);
        $user->setEmail($email);
        $user->setPhone($phone);
        $user->setWebsite($website);
        $this->user = $user;

<<<<<<< Updated upstream
    private function preparePermission(IUser $contextUser): IPermission {
        /** @var IPermission $permission */
        $permission = $this->permissionManager->getPermission(Application::PERMISSION_UPDATE_PROFILE_IMAGE);
        $context    = new Context();
        $context->addUser($contextUser);
        $permission->setContext($context);
        return $permission;
=======
>>>>>>> Stashed changes
    }

    public function create(): void {
        if (null === $this->user) return;
        $updated = $this->userManager->update($this->user);
        if (false === $updated) return;

        $this->createAndSetResponse(
            IResponse::RESPONSE_CODE_OK
            , [
                "message" => $this->getL10N()->translate("user updated")
            ]
        );

    }

<<<<<<< Updated upstream
    private function valid(): bool {
        if ("" === trim($this->user->getFirstName())) return false;
        if ("" === trim($this->user->getLastName())) return false;
        if (false === $this->userService->validEmail($this->user->getEmail())) return false;
        if ("" === trim($this->user->getPhone())) return false;
        if ("" === trim($this->user->getWebsite())) return false;
        return true;
    }

=======
>>>>>>> Stashed changes
    public function afterCreate(): void {

    }

}
