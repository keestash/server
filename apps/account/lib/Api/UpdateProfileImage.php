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

use DateTime;
use doganoo\SimpleRBAC\Test\DataProvider\Context;
use Keestash;
use Keestash\Api\AbstractApi;
use Keestash\Api\Response\DefaultResponse;
use Keestash\Core\DTO\File\File;
use Keestash\Core\DTO\HTTP;
use Keestash\Core\Service\File\FileService;
use Keestash\Core\Service\File\RawFile\RawFileService;
use KSA\Account\Application\Application;
use KSP\Api\IResponse;
use KSP\Core\DTO\IToken;
use KSP\Core\DTO\IUser;
use KSP\Core\Manager\FileManager\IFileManager;
use KSP\Core\Permission\IPermission;
use KSP\Core\Repository\Permission\IPermissionRepository;
use KSP\Core\Repository\User\IUserRepository;
use KSP\L10N\IL10N;

class UpdateProfileImage extends AbstractApi {

    private $l10n              = null;
    private $userManager       = null;
    private $permissionManager = null;
    /** @var IUser $user */
    private $user           = null;
    private $base64Image    = null;
    private $parameters     = null;
    private $rawFileService = null;
    private $fileManager    = null;
    private $fileService    = null;

    public function __construct(
        IL10N $l10n
        , IUserRepository $userManager
        , IFileManager $fileManager
        , IPermissionRepository $permissionManager
        , RawFileService $rawFileService
        , FileService $fileService
        , ?IToken $token = null
    ) {
        $this->l10n              = $l10n;
        $this->userManager       = $userManager;
        $this->fileManager       = $fileManager;
        $this->permissionManager = $permissionManager;
        $this->rawFileService    = $rawFileService;
        $this->fileService       = $fileService;

        parent::__construct($l10n, $token);
    }

    public function onCreate(array $parameters): void {
        $this->parameters = $parameters;
        $userId           = $params["user_id"] ?? null;
        $this->user       = $this->userManager->getUserById((string) $userId);

        parent::setPermission(
            $this->preparePermission($this->user)
        );

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
        if (null === $this->user) {

            parent::setResponse(
                $this->prepareResponse(
                    IResponse::RESPONSE_CODE_NOT_OK
                    , "No user found"
                )
            );
            return;

        }

        if (null === $this->base64Image) {

            parent::setResponse(
                $this->prepareResponse(
                    IResponse::RESPONSE_CODE_NOT_OK
                    , "no image fiven"
                )
            );
            return;

        }

        $raw = base64_decode($this->base64Image);

        if (false === $raw) {
            parent::setResponse(
                $this->prepareResponse(
                    IResponse::RESPONSE_CODE_NOT_OK
                    , "image is not base64 decoded"
                )
            );
            return;
        }

        $name = $this->fileService->getProfileImageName($this->user);
        $tmppName = sys_get_temp_dir() . $name;
        $put      = @file_put_contents($tmppName, $raw);

        if (false === $put) {
            parent::setResponse(
                $this->prepareResponse(
                    IResponse::RESPONSE_CODE_NOT_OK
                    , "could not write in temp dir"
                )
            );
            return;
        }

        $extensions = $this->rawFileService->getFileExtensions($tmppName);
        $mimeType   = $this->rawFileService->getMimeType($tmppName);

        $file = new File();
        $file->setOwner($this->user);
        $file->setSize(
            filesize($tmppName)
        );
        $file->setExtension(
            $extensions[0]
        );
        $file->setDirectory(Keestash::getServer()->getImageRoot());
        $file->setHash(md5_file($tmppName));
        $file->setMimeType($mimeType);
        $file->setName($name);
        $file->setTemporaryPath($tmppName);
        $file->setCreateTs(new DateTime());
        $file->setContent($raw);

        $written = $this->fileManager->write($file);

        if (false === $written) {
            parent::setResponse(
                $this->prepareResponse(
                    IResponse::RESPONSE_CODE_NOT_OK
                    , "Could not write user image"
                )
            );
            return;
        }

        parent::setResponse(
            $this->prepareResponse(
                IResponse::RESPONSE_CODE_OK
                , "Profile Image Updated"
            )
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

    public function afterCreate(): void {

    }

}