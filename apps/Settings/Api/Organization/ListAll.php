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

use doganoo\PHPAlgorithms\Datastructure\Lists\ArrayList\ArrayList;
use Keestash\Api\Response\JsonResponse;
use KSA\Settings\Repository\IOrganizationRepository;
use KSP\Api\IResponse;
use KSP\Core\DTO\Organization\IOrganization;
use KSP\Core\Repository\User\IUserRepository;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ListAll implements RequestHandlerInterface {

    private IOrganizationRepository $organizationRepository;
    private IUserRepository         $userRepository;

    public function __construct(
        IOrganizationRepository $organizationRepository
        , IUserRepository       $userRepository
    ) {
        $this->organizationRepository = $organizationRepository;
        $this->userRepository         = $userRepository;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface {
        $includeActive = $request->getAttribute('includeInactive', false);
        $includeActive = true === $includeActive || 'true' === $includeActive;
        $userHash      = $request->getAttribute('userHash');

        $organizations = new ArrayList();
        if (false === is_string($userHash)) {
            $organizations = $this->organizationRepository->getAll();
        } else {
            $user          = $this->userRepository->getUserByHash($userHash);
            $organizations = $this->organizationRepository->getAllForUser($user);
        }
        $result = [];

        /**
         * @var IOrganization $organization
         */
        foreach ($organizations as $organization) {
            if (false === $includeActive && $organization->getActiveTs() === null) {
                continue;
            }
            $result[] = $organization;
        }

        usort($result
            , static function (IOrganization $organization1, IOrganization $organization2): int {
                return strcasecmp($organization1->getName(), $organization2->getName());
            }
        );

        return new JsonResponse(
            [
                "organizations" => $result
            ]
            , IResponse::OK
        );
    }

}