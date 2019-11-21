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
use Keestash;
use Keestash\Api\AbstractApi;
use Keestash\Api\Response\DefaultResponse;
use Keestash\Core\DTO\File\File;
use Keestash\Core\DTO\HTTP;
use Keestash\Core\Service\File\FileService;
use KSA\Account\Application\Application;
use KSP\Api\IResponse;
use KSP\Core\DTO\IUser;
use KSP\Core\Manager\FileManager\IFileManager;
use KSP\Core\Permission\IPermission;
use KSP\Core\Repository\Permission\IPermissionRepository;
use KSP\Core\Repository\User\IUserRepository;
use KSP\L10N\IL10N;

class DeleteProfileImage extends AbstractApi {

    private $userManager       = null;
    private $l10n              = null;
    private $permissionManager = null;
    private $fileManager       = null;
    private $fileService       = null;

    public function __construct(
        IFileManager $fileManager
        , IUserRepository $userManager
        , IL10N $l10n
        , IPermissionRepository $permissionManager
        , FileService $fileService
    ) {
        parent::__construct($l10n);

        $this->fileManager       = $fileManager;
        $this->userManager       = $userManager;
        $this->l10n              = $l10n;
        $this->permissionManager = $permissionManager;
        $this->fileService       = $fileService;

    }

    public function onCreate(array $parameters): void {
        $userId     = $parameters["user_id"];
        $user       = $this->userManager->getUserById($userId);
        $permission = $this->preparePermission($user);
        parent::setPermission($permission);

        if (null === $user) {
            $this->prepareResponse(
                IResponse::RESPONSE_CODE_NOT_OK
                , "The user is not found"
            );
            return;
        }

        $file = new File();
        $file->setDirectory(Keestash::getServer()->getImageRoot());
        $file->setName($this->fileService->getProfileImageName($user));

        $removed = $this->fileManager->remove($file);

        if (false === $removed) {
            $this->prepareResponse(
                IResponse::RESPONSE_CODE_NOT_OK
                , "The profile picture could not be removed"
            );
            return;
        }

        $this->prepareResponse(
            IResponse::RESPONSE_CODE_OK
            , "User Image Removed!"
        );

    }

    private function preparePermission(IUser $contextUser): IPermission {
        /** @var IPermission $permission */
        $permission = $this->permissionManager->getPermission(Application::PERMISSION_DELETE_PROFILE_IMAGE);
        $context    = new Context();
        $context->addUser($contextUser);
        $permission->setContext($context);
        return $permission;
    }

    private function prepareResponse(int $code, string $message): void {
        $response = new DefaultResponse();
        $response->setCode(HTTP::OK);
        $response->addMessage($code,
            [
                "message" => $this->l10n->translate($message)
            ]
        );
        parent::setResponse($response);
    }

    public function create(): void {

    }

    public function afterCreate(): void {

    }

}