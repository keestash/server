<?php
declare(strict_types=1);
/**
 * Keestash
 *
 * Copyright (C) <2023> <Dogan Ucar>
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

use DateTimeImmutable;
use doganoo\PHPAlgorithms\Common\Exception\NodeNotFoundException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Keestash\Core\Repository\Instance\InstanceDB;
use Keestash\Exception\File\FileNotFoundException;
use KSP\Api\IResponse;
use KSP\Core\Repository\File\IFileRepository;
use Laminas\Diactoros\Response;
use Laminas\Diactoros\Response\EmptyResponse;
use Laminas\Diactoros\Stream;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;

class Download implements RequestHandlerInterface {

    public function __construct(
        private readonly IFileRepository   $fileRepository
        , private readonly LoggerInterface $logger
        , private readonly InstanceDB      $instanceDB
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface {
        $decoded = JWT::decode(
            (string) $request->getAttribute('jwt')
            , new Key(
                (string) $this->instanceDB->getOption(InstanceDB::OPTION_NAME_INSTANCE_HASH)
                , 'HS256'
            )
        );

        $then = new DateTimeImmutable();
        $then = $then->setTimestamp((int) $decoded->iat + 60);
        if ((new DateTimeImmutable()) > $then) {
            $this->logger->info("outdated key", ['decoded' => $decoded]);
            return new EmptyResponse(IResponse::NOT_FOUND);
        }

        $fileId = (int) $request->getAttribute('fileId');
        try {
            $file = $this->fileRepository->get($fileId);
        } catch (FileNotFoundException|NodeNotFoundException $e) {
            $this->logger->info('file or node not found', ['e' => $e]);
            return new EmptyResponse(IResponse::NOT_FOUND);
        }

        return new Response(
            new Stream($file->getFullPath()),
            IResponse::OK,
            [
                'Content-Description'       => 'File Transfer',
                'Content-Type'              => $file->getMimeType(),
                'Cache-Control'             => 'must-revalidate',
                'Expires'                   => '0',
                'Content-Transfer-Encoding' => 'binary',
                'Content-Disposition'       => 'attachment; filename="' . $file->getName() . '.' . $file->getExtension() . '"',
                'Content-Length'            => $file->getSize(),
                'Pragma'                    => 'public'
            ]
        );
    }

}