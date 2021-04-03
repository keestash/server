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

namespace KSA\Users\Api\File;

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

/**
 * Class ProfilePicture
 * @package KSA\Users\Api\File
 */
class ProfilePicture implements RequestHandlerInterface {

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
        $this->fileService    = $fileService;
        $this->fileManager    = $fileManager;
        $this->rawFileService = $rawFileService;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface {

        $targetId = (int) $request->getQueryParams()['targetId'];

        $user = null;

        $users = $this->userRepository->getAll();
        /** @var IUser $iUser */
        foreach ($users as $iUser) {
            if ($targetId === $iUser->getId()) {
                $user = $iUser;
                break;
            }
        }


        if (null === $user) {
            return LegacyResponse::fromData(
                IResponse::RESPONSE_CODE_NOT_OK
                , [
                    "message" => "No user found"
                ]
            );
        }


        // error in logic: we are trying to read default image
        // from DB which does not exist :(
        $file = $this->fileManager->read(
            $this->rawFileService->stringToUri(
                $this->fileService->getProfileImagePath($user)
            )
        );

        if (null === $file) {
            return LegacyResponse::fromData(
                IResponse::RESPONSE_CODE_NOT_OK
                , [
                    "message" => "No picture found"
                ]
            );
        }

        return LegacyResponse::fromData(
            IResponse::RESPONSE_CODE_OK
            , [file_get_contents("{$file->getDirectory()}/{$file->getName()}")]
            , 200
            , [
                IResponse::HEADER_CONTENT_TYPE => $file->getMimeType()
            ]
        );
    }

}
