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

namespace Keestash\Middleware\Api;

use Keestash\Core\Service\Router\VerificationService;
use KSP\Api\IRequest;
use KSP\Api\IResponse;
use KSP\Core\DTO\Token\IToken;
use KSP\Core\Service\HTTP\IHTTPService;
use KSP\Core\Service\Router\IVerificationService;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class KeestashHeaderMiddleware implements MiddlewareInterface {

    private IVerificationService $verification;
    private IHTTPService         $httpService;

    public function __construct(
        IVerificationService $verification
        , IHTTPService       $httpService
    ) {
        $this->verification = $verification;
        $this->httpService  = $httpService;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {

        if (true === $request->getAttribute(IRequest::ATTRIBUTE_NAME_IS_PUBLIC, false)) {
            return $handler->handle($request);
        }

        $token = $this->verification->verifyToken(
            [
                VerificationService::FIELD_NAME_TOKEN       => $request->getHeader(VerificationService::FIELD_NAME_TOKEN)[0] ?? ''
                , VerificationService::FIELD_NAME_USER_HASH => $request->getHeader(VerificationService::FIELD_NAME_USER_HASH)[0] ?? ''
            ]
        );

        if (null === $token) {
            return new JsonResponse(
                ['session expired']
                , IResponse::UNAUTHORIZED
                , [
                    'x-keestash-authentication' => "true"
                ]
            );
        }

        return $handler->handle(
            $request->withAttribute(IToken::class, $token)
        );
    }

}
