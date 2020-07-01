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

namespace KSA\GeneralApi\Api\Thumbnail;

use Keestash\Api\AbstractApi;
use Keestash\Api\Response\PlainResponse;
use Keestash\Core\Permission\PermissionFactory;
use Keestash\Core\Service\File\FileService;
use Keestash\Core\Service\File\RawFile\RawFileService;
use KSP\Api\IResponse;
use KSP\Core\DTO\IJsonToken;
use KSP\Core\Manager\FileManager\IFileManager;
use KSP\L10N\IL10N;

class File extends AbstractApi {

    private $parameters     = null;
    private $fileManager    = null;
    private $rawFileService = null;
    private $fileService    = null;

    public function __construct(
        IFileManager $fileRepository
        , FileService $fileService
        , RawFileService $rawFileService
        , IL10N $l10n
        , ?IJsonToken $token = null
    ) {
        parent::__construct($l10n, $token);

        $this->fileService    = $fileService;
        $this->fileManager    = $fileRepository;
        $this->rawFileService = $rawFileService;
    }

    public function onCreate(array $parameters): void {
        $this->parameters = $parameters;

        $this->setPermission(
            PermissionFactory::getDefaultPermission()
        );
    }

    public function create(): void {
        // TODO implement

        $file = $this->fileManager->read(
            $this->rawFileService->stringToUri(
                $this->fileService->defaultProfileImage()->getFullPath()
            )
        );

        $defaultResponse = new PlainResponse();
        $defaultResponse->addHeader(
            IResponse::HEADER_CONTENT_TYPE
            , $file->getMimeType()
        );
        $defaultResponse->setMessage(file_get_contents($file->getFullPath()));

        parent::setResponse($defaultResponse);
    }

    public function afterCreate(): void {
        // TODO: Implement afterCreate() method.
    }

}
