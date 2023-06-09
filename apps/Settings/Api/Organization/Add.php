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

namespace KSA\Settings\Api\Organization;

use DateTimeImmutable;
use doganoo\DI\Encryption\User\IUserService;
use Exception;
use Keestash\Api\Response\JsonResponse;
use Keestash\Core\DTO\Organization\Organization;
use KSA\Settings\Service\IOrganizationService;
use KSP\Api\IResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;

class Add implements RequestHandlerInterface {

    public function __construct(
        private readonly IOrganizationService $organizationService
        , private readonly LoggerInterface    $logger
        , private readonly IUserService       $userService
    ) {

    }

    public function handle(ServerRequestInterface $request): ResponseInterface {
        $parameters = (array) $request->getParsedBody();
        $name       = (string) ($parameters["organization"] ?? '');

        if ("" === $name) {
            return new JsonResponse([], IResponse::BAD_REQUEST);
        }

        $organization = new Organization();
        $organization->setName($name);
        $organization->setPassword(
            $this->userService->hashPassword(
                bin2hex(random_bytes(16))
            )
        );
        $organization->setCreateTs(new DateTimeImmutable());
        $organization->setActiveTs(new DateTimeImmutable());

        try {
            $organization = $this->organizationService->add($organization);
        } catch (Exception $exception) {
            $this->logger->error($exception->getMessage() . ' ' . $exception->getTraceAsString());
            return new JsonResponse(
                ['error while adding organization']
                , IResponse::INTERNAL_SERVER_ERROR
            );
        }

        return new JsonResponse(
            [
                "organization" => $organization
            ]
            , IResponse::OK
        );
    }

}