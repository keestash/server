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

use Keestash\Api\AbstractApi;
use Keestash\Core\Manager\DataManager\DataManager;

use KSA\PasswordManager\Application\Application;
use KSA\PasswordManager\Repository\Node\FileRepository;
use KSP\Api\IResponse;
use KSP\Core\DTO\Token\IToken;
use KSP\Core\ILogger\ILogger;
use KSP\Core\Repository\File\IFileRepository;
use KSP\L10N\IL10N;

class Remove extends AbstractApi {

    private const CONTEXT = "node_attachments";

    private IFileRepository $fileRepository;
    private DataManager     $dataManager;
    private FileRepository  $nodeFileRepository;
    private ILogger         $logger;

    public function __construct(
        IFileRepository $fileRepository
        , IL10N $l10n
        , FileRepository $nodeFileRepository
        , ILogger $logger
        , ?IToken $token = null
    ) {
        parent::__construct($l10n, $token);

        $this->nodeFileRepository = $nodeFileRepository;
        $this->fileRepository     = $fileRepository;
        $this->logger             = $logger;
        $this->dataManager        = new DataManager(
            Application::APP_ID
            , Remove::CONTEXT
        );

    }

    public function onCreate(array $parameters): void {

    }

    public function create(): void {

        $fileId = $this->getParameter("fileId", null);

        if (null === $fileId) {

            $this->createAndSetResponse(
                IResponse::RESPONSE_CODE_NOT_OK
                , [
                    "message" => $this->getL10N()->translate("no subject id given")
                ]
            );

            return;
        }

        $file = $this->fileRepository->get((int) $fileId);

        if (null == $file) {
            $this->createAndSetResponse(
                IResponse::RESPONSE_CODE_NOT_OK
                , [
                    "message" => $this->getL10N()->translate("no file found")
                ]
            );

            return;
        }

        $removed = $this->dataManager->remove($file);

        if (false === $removed) {
            $this->createAndSetResponse(
                IResponse::RESPONSE_CODE_NOT_OK
                , [
                    "message" => $this->getL10N()->translate("could not remove from file system")
                ]
            );

            return;
        }

        $removed = $this->nodeFileRepository->removeByFile($file);

        if (false === $removed) {
            $this->createAndSetResponse(
                IResponse::RESPONSE_CODE_NOT_OK
                , [
                    "message" => $this->getL10N()->translate("could unlink to node")
                ]
            );

            return;
        }

        $removed = $this->fileRepository->remove($file);

        if (false === $removed) {
            $this->createAndSetResponse(
                IResponse::RESPONSE_CODE_NOT_OK
                , [
                    "message" => $this->getL10N()->translate("could not remove")
                ]
            );

            return;
        }

        $this->createAndSetResponse(
            IResponse::RESPONSE_CODE_OK
            , [
                "message" => $this->getL10N()->translate("file removed")
                , "file"  => $file
            ]
        );

    }

    public function afterCreate(): void {

    }

}
