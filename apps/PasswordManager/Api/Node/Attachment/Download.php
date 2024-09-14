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
use Hoa\Compiler\Exception\IllegalToken;
use Keestash\Core\Repository\Instance\InstanceDB;
use Keestash\Exception\File\FileNotFoundException;
use KSA\Activity\Service\IActivityService;
use KSA\PasswordManager\ConfigProvider;
use KSA\PasswordManager\Repository\Node\FileRepository;
use KSP\Api\IResponse;
use KSP\Core\DTO\Token\IToken;
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
        private readonly IFileRepository    $fileRepository
        , private readonly FileRepository   $nodeFileRepository
        , private readonly LoggerInterface  $logger
        , private readonly InstanceDB       $instanceDB
        , private readonly IActivityService $activityService
    ) {
    }

    #[\Override]
    public function handle(ServerRequestInterface $request): ResponseInterface {
//        /** @var IToken $token */
//        $token   = $request->getAttribute(IToken::class);
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
            $node = $this->nodeFileRepository->getNode($file);
        } catch (FileNotFoundException|NodeNotFoundException $e) {
            $this->logger->info('file or node not found', ['e' => $e]);
            return new EmptyResponse(IResponse::NOT_FOUND);
        }

//        $this->activityService->insertActivityWithSingleMessage(
//            ConfigProvider::APP_ID
//            , (string) $node->getId()
//            , sprintf(
//                '%s downloaded by %s'
//                , $file->getName()
//                , $token->getUser()->getName()
//            )
//        );

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