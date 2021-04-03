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

namespace KSA\GeneralApi\Api;

use Keestash\Api\Response\LegacyResponse;
use Keestash\Core\Manager\FileManager\FileManager;
use Keestash\Core\Service\File\FileService;
use Keestash\Core\Service\File\RawFile\RawFileService;
use KSP\Api\IResponse;
use KSP\Core\DTO\User\IUser;
use KSP\Core\Repository\User\IUserRepository;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class UserList implements RequestHandlerInterface {

    public const USER_TYPE_ALL  = "all";
    public const USER_TYPE_SEEN = "seen";

    private IUserRepository $userRepository;
    private FileService     $fileService;
    private RawFileService  $rawFileService;
    private FileManager     $fileManager;

    public function __construct(
        IUserRepository $userRepository
        , FileService $fileService
        , RawFileService $rawFileService
        , FileManager $fileManager
    ) {
        $this->userRepository = $userRepository;
        $this->rawFileService = $rawFileService;
        $this->fileService    = $fileService;
        $this->fileManager    = $fileManager;
    }


    public function handle(ServerRequestInterface $request): ResponseInterface {
        $all          = $this->userRepository->getAll();
        $pictureTable = [];

        /** @var IUser $user */
        foreach ($all as $key => $user) {

            $picture = $this->fileManager->read(
                $this->rawFileService->stringToUri(
                    $this->fileService->getProfileImagePath($user)
                )
            );

            $picture = $this->rawFileService->stringToBase64($picture->getFullPath());

            $pictureTable[$user->getId()] = $picture;
        }

        return LegacyResponse::fromData(
            IResponse::RESPONSE_CODE_OK
            , [
                "user_list"  => $all
                , "pictures" => $pictureTable
            ]
        );
    }

}
