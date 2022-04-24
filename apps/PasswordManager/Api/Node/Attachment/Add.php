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

namespace KSA\PasswordManager\Api\Node\Attachment;

use DateTime;
use Keestash\Api\Response\LegacyResponse;
use Keestash\Core\DTO\Http\JWT\Audience;
use Keestash\Core\Manager\DataManager\DataManager;
use Keestash\Exception\AccessDeniedException;
use KSA\PasswordManager\ConfigProvider;
use KSA\PasswordManager\Entity\File\NodeFile;
use KSA\PasswordManager\Entity\Password\Credential;
use KSA\PasswordManager\Exception\Node\Credential\CredentialException;
use KSA\PasswordManager\Exception\Node\Credential\NoFileException;
use KSA\PasswordManager\Exception\PasswordManagerException;
use KSA\PasswordManager\Repository\Node\FileRepository;
use KSA\PasswordManager\Repository\Node\NodeRepository;
use KSA\PasswordManager\Service\AccessService;
use KSP\Api\IResponse;
use KSP\Core\DTO\Http\JWT\IAudience;
use KSP\Core\DTO\Token\IToken;
use KSP\Core\ILogger\ILogger;
use KSP\Core\Repository\File\IFileRepository;
use KSP\Core\Service\File\Upload\IFileService;
use KSP\Core\Service\HTTP\IJWTService;
use Laminas\Config\Config;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UploadedFileInterface;
use Psr\Http\Server\RequestHandlerInterface;

class Add implements RequestHandlerInterface {

    public const FIELD_NAME_NAME     = "name";
    public const FIELD_NAME_TYPE     = "type";
    public const FIELD_NAME_TMP_NAME = "tmp_name";
    public const FIELD_NAME_ERROR    = "error";
    public const FIELD_NAME_SIZE     = "size";

    private const REQUIRED_FIELDS = [
        0   => Add::FIELD_NAME_NAME
        , 1 => Add::FIELD_NAME_TYPE
        , 2 => Add::FIELD_NAME_TMP_NAME
        , 3 => Add::FIELD_NAME_ERROR
        , 4 => Add::FIELD_NAME_SIZE
    ];
    private const CONTEXT         = "node_attachments";

    private IFileRepository $fileRepository;
    private NodeRepository  $nodeRepository;
    private DataManager     $dataManager;
    private FileRepository  $nodeFileRepository;
    private IFileService    $uploadFileService;
    private ILogger         $logger;
    private IJWTService     $jwtService;
    private AccessService   $accessService;

    public function __construct(
        IFileRepository  $uploadFileRepository
        , NodeRepository $nodeRepository
        , FileRepository $nodeFileRepository
        , IFileService   $uploadFileService
        , ILogger        $logger
        , Config         $config
        , IJWTService    $jwtService
        , AccessService  $accessService
    ) {
        $this->fileRepository     = $uploadFileRepository;
        $this->nodeRepository     = $nodeRepository;
        $this->nodeFileRepository = $nodeFileRepository;
        $this->uploadFileService  = $uploadFileService;
        $this->logger             = $logger;
        $this->jwtService         = $jwtService;
        $this->accessService      = $accessService;
        $this->dataManager        = new DataManager(
            ConfigProvider::APP_ID
            , $config
            , Add::CONTEXT
        );
    }

    public function handle(ServerRequestInterface $request): ResponseInterface {
        $parameters = (array) $request->getParsedBody();
        $nodeId     = $parameters["node_id"] ?? null;
        $fileList   = $request->getUploadedFiles();

        $fileCount      = count($fileList);
        $processedFiles = [];
        /** @var IToken $token */
        $token = $request->getAttribute(IToken::class);

        if (0 === $fileCount) {
            throw new NoFileException();
        }

        if (null === $nodeId) {
            throw new CredentialException();
        }

        try {
            $node = $this->nodeRepository->getNode((int) $nodeId);
        } catch (PasswordManagerException $exception) {
            // legacy reasons
            throw new CredentialException();
        }

        if (false === ($node instanceof Credential)) {
            throw new CredentialException();
        }

        if (false === $this->accessService->hasAccess($node, $token->getUser())) {
            throw new AccessDeniedException();
        }

        /** @var UploadedFileInterface $file */
        foreach ($fileList as $file) {
            // TODO check content
            // TODO allowed extensions

            $file    = $this->uploadFileService->toFile($file);
            $isValid = $this->uploadFileService->validateUploadedFile($file);

            if (false === $isValid) {
                $this->logger->error('invalid file with name ' . $file->getClientFilename());
                continue;
            }
            $coreFile = $this->uploadFileService->toCoreFile($file);
            $coreFile->setDirectory(
                $this->dataManager->getPath()
            );
            $coreFile->setOwner(
                $token->getUser()
            );

            $moved = $this->uploadFileService->moveUploadedFile($coreFile);

            if (false === $moved) {
                $this->logger->error("could not move {$coreFile->getName()}");
            }

            $nodeFile = new NodeFile();
            $nodeFile->setNode($node);
            $nodeFile->setFile($coreFile);
            $nodeFile->setCreateTs(new DateTime());
            $nodeFile->setJwt(
                $this->jwtService->getJWT(
                    new Audience(
                        IAudience::TYPE_ASSET
                        , $nodeFile->getFile()->getExtension()
                    )
                )
            );
            $nodeFile->setType(NodeFile::FILE_TYPE_ATTACHMENT);

            $id = $this->fileRepository->add($nodeFile->getFile());
            $nodeFile->getFile()->setId($id);

            $connected = $this->nodeFileRepository->connectFileToNode($nodeFile);

            if (false === $connected) {
                // TODO clear DB
                $this->logger->error("could not connect {$nodeFile->getFile()->getId()} to {$nodeFile->getNode()->getId()}");
                continue;
            }
            $processedFiles[] = $nodeFile;
        }

        if (count($processedFiles) > 0) {
            $node->setUpdateTs(new DateTime());
            $this->nodeRepository->updateCredential($node);
        }

        return LegacyResponse::fromData(
            IResponse::RESPONSE_CODE_OK
            , [
                "files" => $processedFiles
            ]
        );
    }

}
