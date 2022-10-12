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
use Keestash\Api\Response\JsonResponse;
use Keestash\Core\DTO\Http\JWT\Audience;
use Keestash\Exception\File\FileNotCreatedException;
use KSA\PasswordManager\ConfigProvider;
use KSA\PasswordManager\Entity\File\NodeFile;
use KSA\PasswordManager\Entity\Node\Credential\Credential;
use KSA\PasswordManager\Exception\Node\Credential\CredentialException;
use KSA\PasswordManager\Exception\PasswordManagerException;
use KSA\PasswordManager\Repository\Node\FileRepository;
use KSA\PasswordManager\Repository\Node\NodeRepository;
use KSP\Api\IResponse;
use KSP\Core\DTO\File\IFile;
use KSP\Core\DTO\Http\JWT\IAudience;
use KSP\Core\DTO\Token\IToken;
use KSP\Core\Repository\File\IFileRepository;
use KSP\Core\Service\Core\Data\IDataService;
use KSP\Core\Service\File\Upload\IFileService;
use KSP\Core\Service\HTTP\IJWTService;
use KSP\Core\Service\Logger\ILogger;
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

    private const REQUIRED_FIELDS          = [
        0   => Add::FIELD_NAME_NAME
        , 1 => Add::FIELD_NAME_TYPE
        , 2 => Add::FIELD_NAME_TMP_NAME
        , 3 => Add::FIELD_NAME_ERROR
        , 4 => Add::FIELD_NAME_SIZE
    ];
    public const  CONTEXT                  = "node_attachments";
    private const ERROR_NOT_INSERTED_IN_DB = 0;
    private const ERROR_NOT_CONNECTED      = 1;

    private IFileRepository $fileRepository;
    private NodeRepository $nodeRepository;
    private IDataService   $dataManager;
    private FileRepository $nodeFileRepository;
    private IFileService    $uploadFileService;
    private ILogger         $logger;
    private IJWTService     $jwtService;
    private Config          $config;

    public function __construct(
        IFileRepository  $uploadFileRepository
        , NodeRepository $nodeRepository
        , FileRepository $nodeFileRepository
        , IFileService   $uploadFileService
        , ILogger        $logger
        , Config         $config
        , IJWTService    $jwtService
        , IDataService   $dataManager
    ) {
        $this->fileRepository     = $uploadFileRepository;
        $this->nodeRepository     = $nodeRepository;
        $this->nodeFileRepository = $nodeFileRepository;
        $this->uploadFileService  = $uploadFileService;
        $this->logger             = $logger;
        $this->jwtService         = $jwtService;
        $this->config             = $config;
        $this->dataManager        = $dataManager;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface {
        $parameters = (array) $request->getParsedBody();
        $nodeId     = $parameters["node_id"] ?? null;
        $fileList   = $request->getUploadedFiles();

        $fileCount      = count($fileList);
        $processedFiles = [];
        $errorFiles     = [];
        /** @var IToken $token */
        $token             = $request->getAttribute(IToken::class);
        $allowedExtensions = $this->config->get(ConfigProvider::FILE_UPLOAD_ALLOWED_EXTENSIONS)->toArray();

        if (0 === $fileCount) {
            return new JsonResponse(
                []
                , IResponse::BAD_REQUEST
            );
        }

        if (null === $nodeId) {
            return new JsonResponse(
                []
                , IResponse::BAD_REQUEST
            );
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

        /** @var UploadedFileInterface $file */
        foreach ($fileList as $file) {
            $file    = $this->uploadFileService->toFile($file);
            $result  = $this->uploadFileService->validateUploadedFile($file);
            $isValid = $result->getResults()->length() === 0;

            if (false === $isValid) {
                $this->logger->error('invalid file with name ' . $file->getClientFilename());
                $errorFiles[] = $file;
                continue;
            }
            $coreFile = $this->uploadFileService->toCoreFile($file);
            $coreFile->setDirectory(
                $this->dataManager->getPath()
            );
            $coreFile->setOwner(
                $token->getUser()
            );

            if (false === in_array($coreFile->getExtension(), $allowedExtensions, true)) {
                $this->logger->warning('file with extension not allowed', ['extension' => $coreFile->getExtension()]);
                $errorFiles[] = $file;
                continue;
            }

            $moved = $this->uploadFileService->moveUploadedFile($coreFile);

            if (false === $moved) {
                $this->logger->error("could not move {$coreFile->getName()}");
                $errorFiles[] = $file;
                continue;
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

            try {
                $file = $this->fileRepository->add($nodeFile->getFile());
            } catch (FileNotCreatedException $exception) {
                $this->logger->error('error with file creation', ['exception' => $exception, 'name' => $coreFile->getName()]);
                $this->removeFile(
                    Add::ERROR_NOT_INSERTED_IN_DB
                    , $coreFile
                );
                $errorFiles[] = $file;
                continue;
            }

            $nodeFile->getFile()->setId($file->getId());

            $connected = $this->nodeFileRepository->connectFileToNode($nodeFile);

            if (false === $connected) {
                $this->removeFile(
                    Add::ERROR_NOT_CONNECTED
                    , $coreFile
                );
                $this->logger->error("could not connect {$nodeFile->getFile()->getId()} to {$nodeFile->getNode()->getId()}");
                $errorFiles[] = $file;
                continue;
            }
            $processedFiles[] = $nodeFile;
        }

        if (count($processedFiles) > 0) {
            $node->setUpdateTs(new DateTime());
            $this->nodeRepository->updateCredential($node);
        }

        return new JsonResponse(
            [
                "files"   => $processedFiles
                , "error" => $errorFiles
            ]
            , IResponse::OK
        );
    }

    private function removeFile(int $type, IFile $file): void {
        $this->uploadFileService->removeUploadedFile($file);
        if ($type === 0) return;
        $this->fileRepository->remove($file);
    }

}
