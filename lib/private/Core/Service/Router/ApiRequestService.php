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

use Keestash\Core\DTO\Instance\Request\APIRequest;
use KSP\Api\IRequest;
use KSP\Core\DTO\Token\IToken;
use KSP\Core\Repository\ApiLog\IApiLogRepository;
use KSP\Core\Service\Router\IApiRequestService;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

final readonly class ApiRequestService implements IApiRequestService {

    public function __construct(
        private IApiLogRepository $apiLogRepository
        , private LoggerInterface $logger
    ) {
    }

    /**
     * @param ServerRequestInterface $request
     * @return void
     */
    public function log(ServerRequestInterface $request): void {
        $isPublic = $request->getAttribute(IRequest::ATTRIBUTE_NAME_IS_PUBLIC);

        if (true === $isPublic) {
            $this->logger->debug('route is public, no log inserted');
            return;
        }

        $token = $request->getAttribute(IToken::class);

        if (null === $token) {
            $this->logger->emergency('route is not public but token not found', ['route'=>$request->getAttribute(IRequest::ATTRIBUTE_NAME_MATCHED_PATH)]);
            return;
        }

        $logged = $this->apiLogRepository->log(
            new APIRequest(
                $request->getAttribute(IToken::class),
                $request->getAttribute(IRequest::ATTRIBUTE_NAME_APPLICATION_START),
                $request->getAttribute(IRequest::ATTRIBUTE_NAME_APPLICATION_END),
                $request->getAttribute(IRequest::ATTRIBUTE_NAME_MATCHED_PATH)
            )
        );
        $this->logger->debug('api request log created', ['log' => $logged]);
    }

}
