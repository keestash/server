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

use Keestash;
use Keestash\Api\AbstractApi;
use Keestash\Api\Response\PlainResponse;
use Keestash\Core\Manager\FileManager\FileManager;

use Keestash\Core\Service\File\FileService;
use Keestash\Core\Service\File\RawFile\RawFileService;
use KSP\Api\IResponse;
use KSP\Core\DTO\Token\IToken;
use KSP\Core\DTO\User\IUser;
use KSP\Core\Repository\User\IUserRepository;
use KSP\L10N\IL10N;

/**
 * Class ProfilePicture
 * @package KSA\Users\Api\File
 */
class ProfilePicture extends AbstractApi {

    private IUserRepository $userRepository;
    private FileService     $fileService;
    private RawFileService  $rawFileService;
    private FileManager     $fileManager;

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
        $this->fileService    = $fileService;
        $this->fileManager    = $fileManager;
        $this->rawFileService = $rawFileService;
    }

    public function onCreate(array $parameters): void {

    }

    public function create(): void {

        $targetId = (int) $this->getParameter('targetId');

        $user = null;

        $users = Keestash::getServer()->getUsersFromCache();
        /** @var IUser $iUser */
        foreach ($users as $iUser) {
            if ($targetId === $iUser->getId()) {
                $user = $iUser;
                break;
            }
        }

        
        if (null === $user) {
            $response = parent::createResponse(
                IResponse::RESPONSE_CODE_NOT_OK
                , [
                    "message" => "No user found"
                ]
            );
            parent::setResponse($response);
            return;
        }

        
        // error in logic: we are trying to read default image
        // from DB which does not exist :(
        $file = $this->fileManager->read(
            $this->rawFileService->stringToUri(
                $this->fileService->getProfileImagePath($user)
            )
        );

        
        if (null === $file) {
            $response = parent::createResponse(
                IResponse::RESPONSE_CODE_NOT_OK
                , [
                    "message" => "No picture found"
                ]
            );
            parent::setResponse($response);
            return;
        }

        
        $defaultResponse = new PlainResponse();
        $defaultResponse->addHeader(
            IResponse::HEADER_CONTENT_TYPE
            , $file->getMimeType()
        );
//        $defaultResponse->setMessage(file_get_contents($file->getFullPath()));
        $defaultResponse->setMessage(file_get_contents("{$file->getDirectory()}/{$file->getName()}"));

        
        parent::setResponse($defaultResponse);
    }

    public function afterCreate(): void {

        
    }

}
