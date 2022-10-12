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
use KSA\PasswordManager\Repository\Node\FileRepository;
use KSA\PasswordManager\Service\AccessService;
use KSP\Api\IResponse;
use KSP\Core\DTO\Token\IToken;
use KSP\Core\Manager\Data\IDataManager;
use KSP\Core\Repository\File\IFileRepository;
use KSP\Core\Service\L10N\IL10N;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class Remove implements RequestHandlerInterface {

    public const CONTEXT = "node_attachments";

    private IFileRepository $fileRepository;
    private IDataManager    $dataManager;
    private FileRepository  $nodeFileRepository;
    private IL10N           $translator;
    private AccessService   $accessService;

    public function __construct(
        IFileRepository  $fileRepository
        , IL10N          $l10n
        , FileRepository $nodeFileRepository
        , AccessService  $accessService
        , IDataManager   $dataManager
    ) {
        $this->translator         = $l10n;
        $this->nodeFileRepository = $nodeFileRepository;
        $this->fileRepository     = $fileRepository;
        $this->accessService      = $accessService;
        $this->dataManager        = $dataManager;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface {
        $parameters = (array) $request->getParsedBody();
        $fileId     = $parameters["fileId"] ?? -1;
        $user       = $request->getAttribute(IToken::class)->getUser();

        try {
            $file = $this->fileRepository->get((int) $fileId);
        } catch (FileNotFoundException $exception) {
            return new JsonResponse([], IResponse::NOT_FOUND);
        }

        $node = $this->nodeFileRepository->getNode($file);

        if (false === $this->accessService->hasAccess($node, $user)) {
            return new JsonResponse([], IResponse::FORBIDDEN);
        }

        $removed = $this->dataManager->remove($file);

        if (false === $removed) {
            return new JsonResponse([
                "message" => $this->translator->translate("could not remove from file system")
            ], IResponse::NOT_MODIFIED);
        }

        $removed = $this->nodeFileRepository->removeByFile($file);

        if (false === $removed) {
            return new JsonResponse([
                "message" => $this->translator->translate("could unlink to node")
            ], IResponse::NOT_MODIFIED);
        }

        try {
            $this->fileRepository->remove($file);
        } catch (FileNotDeletedException $exception) {
            return new JsonResponse(
                [
                    "message" => $this->translator->translate("could not remove")
                ]
                , IResponse::NOT_MODIFIED
            );
        }

        return new JsonResponse(
            [
                "message" => $this->translator->translate("file removed")
                , "file"  => $file
            ]
            , IResponse::OK
        );

    }

}
