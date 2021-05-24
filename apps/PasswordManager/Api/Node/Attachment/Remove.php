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

use Keestash\Api\Response\LegacyResponse;
use Keestash\Core\Manager\DataManager\DataManager;
use KSA\PasswordManager\ConfigProvider;
use KSA\PasswordManager\Repository\Node\FileRepository;
use KSP\Api\IResponse;
use KSP\Core\Repository\File\IFileRepository;
use KSP\L10N\IL10N;
use Laminas\Config\Config;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class Remove implements RequestHandlerInterface {

    private const CONTEXT = "node_attachments";

    private IFileRepository $fileRepository;
    private DataManager     $dataManager;
    private FileRepository  $nodeFileRepository;
    private IL10N           $translator;

    public function __construct(
        IFileRepository $fileRepository
        , IL10N $l10n
        , FileRepository $nodeFileRepository
        , Config $config
    ) {
        $this->translator         = $l10n;
        $this->nodeFileRepository = $nodeFileRepository;
        $this->fileRepository     = $fileRepository;

        $this->dataManager = new DataManager(
            ConfigProvider::APP_ID
            , $config
            , Remove::CONTEXT
        );
    }

    public function handle(ServerRequestInterface $request): ResponseInterface {
        $parameters = json_decode((string) $request->getBody(), true);
        $fileId     = $parameters["fileId"] ?? null;

        if (null === $fileId) {

            return LegacyResponse::fromData(
                IResponse::RESPONSE_CODE_NOT_OK
                , [
                    "message" => $this->translator->translate("no subject id given")
                ]
            );

        }

        $file = $this->fileRepository->get((int) $fileId);

        if (null == $file) {
            return LegacyResponse::fromData(
                IResponse::RESPONSE_CODE_NOT_OK
                , [
                    "message" => $this->translator->translate("no file found")
                ]
            );
        }

        $removed = $this->dataManager->remove($file);

        if (false === $removed) {
            return LegacyResponse::fromData(
                IResponse::RESPONSE_CODE_NOT_OK
                , [
                    "message" => $this->translator->translate("could not remove from file system")
                ]
            );
        }

        $removed = $this->nodeFileRepository->removeByFile($file);

        if (false === $removed) {
            return LegacyResponse::fromData(
                IResponse::RESPONSE_CODE_NOT_OK
                , [
                    "message" => $this->translator->translate("could unlink to node")
                ]
            );

        }

        $removed = $this->fileRepository->remove($file);

        if (false === $removed) {
            return LegacyResponse::fromData(
                IResponse::RESPONSE_CODE_NOT_OK
                , [
                    "message" => $this->translator->translate("could not remove")
                ]
            );
        }

        return LegacyResponse::fromData(
            IResponse::RESPONSE_CODE_OK
            , [
                "message" => $this->translator->translate("file removed")
                , "file"  => $file
            ]
        );

    }

}
