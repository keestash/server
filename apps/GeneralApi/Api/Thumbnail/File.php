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

use Keestash\Core\Service\File\FileService;
use Keestash\Core\Service\File\RawFile\RawFileService;
use Keestash\Exception\InstanceNotInstalledException;
use KSP\Api\IResponse;
use KSP\Core\Manager\FileManager\IFileManager;
use Laminas\Diactoros\Response\TextResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class File implements RequestHandlerInterface {

    private IFileManager   $fileManager;
    private RawFileService $rawFileService;
    private FileService    $fileService;

    public function __construct(
        IFileManager $fileManager
        , FileService $uploadFileService
        , RawFileService $rawFileService
    ) {
        $this->fileService    = $uploadFileService;
        $this->fileManager    = $fileManager;
        $this->rawFileService = $rawFileService;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface {

        $file = $this->fileManager->read(
            $this->rawFileService->stringToUri(
                $this->fileService->getDefaultImage()->getFullPath()
            )
        );

        if (null === $file) {
            throw new InstanceNotInstalledException();
        }

        return new TextResponse(
            (string) file_get_contents($file->getFullPath())
            , 200
            , [
                IResponse::HEADER_CONTENT_TYPE => $file->getMimeType()
            ]
        );
    }

}
