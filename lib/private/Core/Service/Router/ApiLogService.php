<?php
declare(strict_types=1);
/**
 * Keestash
 *
 * Copyright (C) <2021> <Dogan Ucar>
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

namespace Keestash\Core\Service\Router;

use DateTimeImmutable;
use doganoo\PHPAlgorithms\Datastructure\Lists\ArrayList\ArrayList;
use Keestash\Core\DTO\Instance\Request\ApiLog;
use KSP\Api\IRequest;
use KSP\Core\DTO\Instance\Request\ApiLogInterface;
use KSP\Core\DTO\User\IUser;
use KSP\Core\Repository\ApiLog\IApiLogRepository;
use KSP\Core\Service\Router\ApiLogServiceInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;

final readonly class ApiLogService implements ApiLogServiceInterface {

    public function __construct(
        private IApiLogRepository $apiLogRepository
        , private LoggerInterface $logger
    ) {
    }

    /**
     * @param ServerRequestInterface $request
     * @return void
     */
    #[\Override]
    public function log(ServerRequestInterface $request): void {
        $isPublic = $request->getAttribute(IRequest::ATTRIBUTE_NAME_IS_PUBLIC);

        if (true === $isPublic) {
            $this->logger->debug('route is public, no log inserted');
            return;
        }

        $logged = $this->apiLogRepository->log(
            new ApiLog(
                Uuid::uuid4()->toString(),
                $request->getAttribute(IRequest::ATTRIBUTE_NAME_REQUEST_ID),
                (string) json_encode(
                    array_merge(
                        [
                            'method'      => $request->getMethod(),
                            'matchedPath' => $request->getAttribute(IRequest::ATTRIBUTE_NAME_MATCHED_PATH)
                        ],
                        $request->getHeaders()
                    )
                ),
                $request->getAttribute(IRequest::ATTRIBUTE_NAME_APPLICATION_START),
                $request->getAttribute(IRequest::ATTRIBUTE_NAME_APPLICATION_END),
                new DateTimeImmutable()
            )
        );
        $this->logger->debug('api request log created', ['log' => $logged]);
    }

    #[\Override]
    public function filterUser(IUser $user, ArrayList $logs): ArrayList {
        $list = new ArrayList();

        /** @var ApiLogInterface $log */
        foreach ($logs as $log) {
            /** @var array<string|array> $data */
            $data     = (array) json_decode($log->getData(), true, 512, JSON_THROW_ON_ERROR);
            $userHash = $data[VerificationService::FIELD_NAME_USER_HASH][0] ?? null;

            if ($user->getHash() === $userHash) {
                $list->add(
                    new ApiLog(
                        $log->getId(),
                        $log->getRequestId(),
                        $log->getData(),
                        clone $log->getStart(),
                        clone $log->getEnd(),
                        clone $log->getCreateTs()
                    )
                );
            }
        }

        return $list;
    }

}
