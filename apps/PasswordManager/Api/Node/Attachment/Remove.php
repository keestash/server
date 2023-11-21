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

use Keestash\Api\Response\JsonResponse;
use Keestash\Exception\File\FileNotDeletedException;
use Keestash\Exception\File\FileNotFoundException;
use Keestash\Exception\Repository\NoRowsFoundException;
use KSA\PasswordManager\Exception\NodeFileException;
use KSA\PasswordManager\Repository\Node\FileRepository;
use KSA\PasswordManager\Service\AccessService;
use KSP\Api\IResponse;
use KSP\Core\DTO\Token\IToken;
use KSP\Core\Repository\File\IFileRepository;
use KSP\Core\Service\Core\Data\IDataService;
use KSP\Core\Service\L10N\IL10N;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;

class Remove implements RequestHandlerInterface {

    public const CONTEXT = "node_attachments";

    public function __construct(
        private readonly IFileRepository   $fileRepository
        , private readonly FileRepository  $nodeFileRepository
        , private readonly AccessService   $accessService
        , private readonly IDataService    $dataManager
        , private readonly LoggerInterface $logger
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface {
        $parameters = (array) $request->getParsedBody();
        $nodeId     = (int) $parameters['node']['id'];
        $fileId     = (int) $parameters['file']['id'];
        $user       = $request->getAttribute(IToken::class)->getUser();

        try {
            $file = $this->fileRepository->get($fileId);
        } catch (FileNotFoundException) {
            return new JsonResponse([], IResponse::NOT_FOUND);
        }

        $node = $this->nodeFileRepository->getNode($file);

        if (false === $this->accessService->hasAccess($node, $user)) {
            return new JsonResponse([], IResponse::FORBIDDEN);
        }

        if ($nodeId !== $node->getId()) {
            return new JsonResponse([], IResponse::FORBIDDEN);
        }

        try {
            $this->nodeFileRepository->startTransaction();
            $this->nodeFileRepository->removeByFile($file);

            $this->fileRepository->startTransaction();
            $this->fileRepository->remove($file);

            $this->dataManager->remove($file);
            $this->nodeFileRepository->endTransaction();
            $this->fileRepository->endTransaction();

            return new JsonResponse([], IResponse::OK);

        } catch (NodeFileException|FileNotDeletedException|FileNotFoundException $e) {
            $this->logger->error('error removing file', ['exception' => $e]);
            $this->nodeFileRepository->rollBack();
            $this->fileRepository->rollBack();
            return new JsonResponse([], IResponse::NOT_MODIFIED);
        }

    }

}
