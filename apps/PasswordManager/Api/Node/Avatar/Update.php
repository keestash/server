<?php
declare(strict_types=1);
/**
 * server
 *
 * Copyright (C) <2020> <Dogan Ucar>
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

namespace KSA\PasswordManager\Api\Node\Avatar;

use Keestash\Api\Response\LegacyResponse;
use Keestash\Core\Manager\DataManager\DataManager;
use Keestash\Core\Service\File\FileService as CoreFileService;
use Keestash\Core\Service\File\RawFile\RawFileService;
use KSA\PasswordManager\Application\Application;
use KSA\PasswordManager\ConfigProvider;
use KSA\PasswordManager\Entity\File\NodeFile;
use KSA\PasswordManager\Exception\Node\Credential\CredentialException;
use KSA\PasswordManager\Exception\Node\Credential\FileNotMovedException;
use KSA\PasswordManager\Exception\Node\Credential\InvalidFileException;
use KSA\PasswordManager\Exception\Node\Credential\NoFileException;
use KSA\PasswordManager\Exception\Node\NodeNotFoundException;
use KSA\PasswordManager\Repository\Node\FileRepository;
use KSA\PasswordManager\Repository\Node\NodeRepository;
use KSP\Api\IResponse;
use KSP\Core\DTO\Token\IToken;
use KSP\Core\Manager\DataManager\IDataManager;
use KSP\Core\Repository\File\IFileRepository;
use KSP\Core\Service\File\Upload\IFileService as IUploadFileService;
use Laminas\Config\Config;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class Update implements RequestHandlerInterface {

    public const CONTEXT = "nodeAvatar";

    private IUploadFileService $uploadFileService;
    private IFileRepository    $fileRepository;
    private IDataManager       $dataManager;
    private FileRepository     $nodeFileRepository;
    private NodeRepository     $nodeRepository;
    private RawFileService     $rawFileService;
    private CoreFileService    $coreFileService;

    public function __construct(
        IUploadFileService $uploadFileService
        , IFileRepository $fileRepository
        , FileRepository $nodeFileRepository
        , NodeRepository $nodeRepository
        , RawFileService $rawFileService
        , CoreFileService $coreFileService
        , Config $config
    ) {
        $this->uploadFileService  = $uploadFileService;
        $this->fileRepository     = $fileRepository;
        $this->nodeFileRepository = $nodeFileRepository;
        $this->nodeRepository     = $nodeRepository;
        $this->rawFileService     = $rawFileService;
        $this->coreFileService    = $coreFileService;
        $this->dataManager        = new DataManager(
            ConfigProvider::APP_ID
            , $config
            , Update::CONTEXT
        );
    }

    public function handle(ServerRequestInterface $request): ResponseInterface {
        $files     = $request->getUploadedFiles();
        $fileCount = count($files);
        /** @var IToken $token */
        $token = $request->getAttribute(IToken::class);

        if (0 === $fileCount) {
            throw new NoFileException();
        }

        $nodeAvatar = $files[0] ?? null;
        $nodeId     = $request->getAttribute("nodeId");

        if (null === $nodeAvatar) {
            throw new InvalidFileException("no file found");
        }

        if (null === $nodeId) {
            throw new NodeNotFoundException("no node found for $nodeId");
        }

        $node = $this->nodeRepository->getNode((int) $nodeId);

        if ($token->getUser()->getId() !== $node->getUser()->getId()) {
            throw new NodeNotFoundException("node does not belong to user");
        }

        $avatarFile = null;
        $fileList   = $this->nodeFileRepository->getFilesPerNode($node);

        /** @var NodeFile $nodeFile */
        foreach ($fileList as $nodeFile) {
            if ($nodeFile->getType() === NodeFile::FILE_TYPE_AVATAR) {
                $avatarFile = $nodeFile;
                break;
            }
        }

        $isValid = $this->uploadFileService->validateUploadedFile($nodeAvatar);
        if (false === $isValid) {
            throw new InvalidFileException("file is invalid");
        }

        $file = $this->uploadFileService->toCoreFile($nodeAvatar);
        $file->setName(
            $this->coreFileService->getAvatarName($node->getId())
        );
        $file->setDirectory(
            $this->dataManager->getPath()
        );
        $file->setOwner(
            $token->getUser()
        );

        $moved = $this->uploadFileService->moveUploadedFile($file);

        if (false === $moved) {
            throw new FileNotMovedException("could not move file");
        }

        if (null === $avatarFile) {
            $newAvatarFile = new NodeFile();
            $newAvatarFile->setFile($file);
            $newAvatarFile->setNode($node);
            $newAvatarFile->setType(NodeFile::FILE_TYPE_AVATAR);

            $id = $this->fileRepository->add($newAvatarFile->getFile());
            $newAvatarFile->getFile()->setId($id);

            $connected = $this->nodeFileRepository->connectFileToNode($newAvatarFile);

            if (false === $connected) {
                throw new CredentialException("could not connect file to node");
            }

            $avatarFile = $newAvatarFile;
        } else {
            $file->setId(
                $avatarFile->getFile()->getId()
            );
            $avatarFile->setFile($file);
            $this->fileRepository->update($avatarFile->getFile());
        }

        return LegacyResponse::fromData(
            IResponse::RESPONSE_CODE_OK
            , [
                "base64" => $this->rawFileService->stringToBase64($avatarFile->getFile()->getFullPath())
            ]
        );
    }

}
