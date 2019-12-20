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

namespace KSA\general_api\lib\Api;

use Keestash\Api\AbstractApi;
use Keestash\Core\Manager\FileManager\FileManager;
use Keestash\Core\Permission\PermissionFactory;
use Keestash\Core\Service\File\FileService;
use Keestash\Core\Service\File\RawFile\RawFileService;
use KSP\Api\IResponse;
use KSP\Core\DTO\IToken;
use KSP\Core\DTO\IUser;
use KSP\Core\Repository\User\IUserRepository;
use KSP\L10N\IL10N;

class UserList extends AbstractApi {

    public const USER_TYPE_ALL  = "all";
    public const USER_TYPE_SEEN = "seen";

    private $parameters     = null;
    private $userRepository = null;
    private $fileService    = null;
    private $rawFileService = null;
    private $fileManager    = null;

    public function __construct(
        IL10N $l10n
        , IUserRepository $userRepository
        , FileService $fileService
        , RawFileService $rawFileService
        , FileManager $fileManager
        , ?IToken $token = null
    ) {
        parent::__construct($l10n, $token);

        $this->userRepository = $userRepository;
        $this->rawFileService = $rawFileService;
        $this->fileService    = $fileService;
        $this->fileManager    = $fileManager;
    }

    public function onCreate(array $parameters): void {
        $this->parameters = $parameters;

        parent::setPermission(
            PermissionFactory::getDefaultPermission()
        );
    }

    public function create(): void {
        $type         = $this->parameters['type'];
        $all          = $this->userRepository->getAll();
        $pictureTable = [];

        /** @var IUser $user */
        foreach ($all as $key => $user) {
            if ($type === UserList::USER_TYPE_SEEN && null === $user->getLastLogin()) {
                $all->remove($key);
            }

            $picture = $this->fileManager->read(
                $this->rawFileService->stringToUri(
                    $this->fileService->getProfileImagePath($user)
                )
            );

            $picture = $this->rawFileService->stringToBase64($picture->getFullPath());

            $pictureTable[$user->getId()] = $picture;
        }


        $response = parent::createResponse(
            IResponse::RESPONSE_CODE_OK
            , [
                "user_list"  => $all
                , "pictures" => $pictureTable
            ]
        );

        parent::setResponse($response);
    }

    public function afterCreate(): void {

    }

}