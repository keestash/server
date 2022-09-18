<?php
declare(strict_types=1);
/**
 * Keestash
 *
 * Copyright (C) <2022> <Dogan Ucar>
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

namespace KSA\Settings\Api\User;

use Keestash\Api\Response\JsonResponse;
use Keestash\Core\DTO\Http\JWT\Audience;
use Keestash\Core\DTO\URI\URI;
use Keestash\Core\DTO\URI\URL\URL;
use Keestash\Core\Service\File\FileService;
use KSA\Settings\ConfigProvider;
use KSP\Api\IResponse;
use KSP\Core\DTO\File\IFile;
use KSP\Core\DTO\Http\JWT\IAudience;
use KSP\Core\DTO\Token\IToken;
use KSP\Core\Repository\File\IFileRepository;
use KSP\Core\Service\File\Upload\IFileService as UploadFileService;
use KSP\Core\Service\HTTP\IJWTService;
use Laminas\Config\Config;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * TODO 1. check the name in DB vs on file system
 */
class UpdateProfileImage implements RequestHandlerInterface {

    private Config            $config;
    private UploadFileService $uploadFileService;
    private IFileRepository   $fileRepository;
    private IJWTService       $jwtService;
    private FileService       $fileService;

    public function __construct(
        Config              $config
        , UploadFileService $uploadFileService
        , IFileRepository   $fileRepository
        , IJWTService       $jwtService
        , FileService       $fileService
    ) {
        $this->config            = $config;
        $this->uploadFileService = $uploadFileService;
        $this->fileRepository    = $fileRepository;
        $this->jwtService        = $jwtService;
        $this->fileService       = $fileService;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface {
        $parameters = (array) $request->getParsedBody();
        $userHash   = $parameters['user_hash'] ?? null;
        /** @var IToken $token */
        $token             = $request->getAttribute(IToken::class);
        $files             = $request->getUploadedFiles();
        $fileCount         = count($files);
        $allowedExtensions = $this->config->get(ConfigProvider::ALLOWED_PROFILE_IMAGE_EXTENSIONS)->toArray();

        if (0 === $fileCount) {
            return new JsonResponse(
                ["no files given"]
                , IResponse::BAD_REQUEST
            );
        }

        if ($token->getUser()->getHash() !== $userHash) {
            return new JsonResponse(
                []
                , IResponse::FORBIDDEN
            );
        }

        $file    = $this->uploadFileService->toFile(array_values($files)[0]);
        $validationResult = $this->uploadFileService->validateUploadedFile($file);

        if ($validationResult->getResults()->length() !== 0) {

            return new JsonResponse(
                ["invalid file"]
                , IResponse::BAD_REQUEST
            );
        }

        $coreFile = $this->uploadFileService->toCoreFile($file);

        if (false === in_array($coreFile->getExtension(), $allowedExtensions, true)) {
            return new JsonResponse(['forbidden extension'], IResponse::FORBIDDEN);
        }

        $directory = $this->config->get(\Keestash\ConfigProvider::IMAGE_PATH) . '/' . md5((string) $token->getUser()->getId()) . '/';

        if (false === is_dir($directory)) {
            mkdir($directory);
        }

        $fileName = $this->fileService->getProfileImageName($token->getUser());

        $coreFile->setDirectory($directory);
        $coreFile->setName($fileName);
        $coreFile->setOwner($token->getUser());

        // if there is a profile image already set, we need to remove it
        $oldImage = $this->fileRepository->getByName($coreFile->getName());

        if (null !== $oldImage) {
            $this->fileRepository->remove($oldImage);
            $this->uploadFileService->removeUploadedFile($oldImage);
        }

        $moved = $this->uploadFileService->moveUploadedFile($coreFile);

        if (false === $moved) {
            return new JsonResponse([], IResponse::INTERNAL_SERVER_ERROR);
        }

        $id = $this->fileRepository->add($coreFile);

        if (null === $id) {
            $this->removeFile($coreFile);
            return new JsonResponse([], IResponse::INTERNAL_SERVER_ERROR);
        }

        $coreFile->setId($id);

        return new JsonResponse(
            [
                "jwt" => $this->jwtService->getJWT(
                    new Audience(
                        IAudience::TYPE_USER
                        , (string) $token->getUser()->getId()
                    )
                )
            ]
            , IResponse::OK
        );
    }

    private function removeFile(IFile $file): void {
        $this->uploadFileService->removeUploadedFile($file);
        $this->fileRepository->remove($file);
    }

}