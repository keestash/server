<?php
declare(strict_types=1);
/**
 * Keestash
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

namespace KST\Service\Factory\Service\File\Upload;

use Keestash\Core\DTO\File\Validation\Result;
use Keestash\Core\Service\Config\IniConfigService;
use Keestash\Core\Service\File\Upload\FileService;
use KSP\Core\Service\File\Upload\IFileService;
use Psr\Log\LoggerInterface;
use Mockery;
use Psr\Container\ContainerInterface;

class FileServiceFactory {

    public function __invoke(ContainerInterface $container): IFileService {
        $fileService = new FileService(
            $container->get(IniConfigService::class)
            , $container->get(LoggerInterface::class)
        );

        $fileService = Mockery::mock($fileService);

        $fileService->shouldReceive('moveUploadedFile')
            ->andReturn(true);
        $fileService->shouldReceive('removeUploadedFile')
            ->andReturn(true);
        $fileService->shouldReceive('validateUploadedFile')
            ->andReturn(new Result());

        return $fileService;
    }

}