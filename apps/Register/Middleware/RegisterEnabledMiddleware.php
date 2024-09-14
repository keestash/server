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

namespace KSA\Register\Middleware;

use Keestash\Api\Response\JsonResponse;
use KSA\Register\Entity\IResponseCodes;
use KSA\Settings\Service\ISettingsService;
use KSP\Api\IResponse;
use KSP\Core\Service\HTTP\IResponseService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;

class RegisterEnabledMiddleware implements MiddlewareInterface {

    public function __construct(
        private readonly LoggerInterface    $logger
        , private readonly ISettingsService $settingsService
        , private readonly IResponseService $responseService
    ) {
    }

    #[\Override]
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {
        $registerEnabled = $this->settingsService->isRegisterEnabled();

        if (false === $registerEnabled) {
            $this->logger->info(
                'register disabled, but tried to consume a register endpoint',
                ['body' => $request->getBody()]
            );
            return new JsonResponse(
                [
                    'responseCode' => $this->responseService->getResponseCode(IResponseCodes::RESPONSE_NAME_REGISTER_DISABLED)
                ]
                , IResponse::BAD_REQUEST
            );
        }
        return $handler->handle($request);
    }

}