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

use Keestash\Api\Response\JsonResponse;
use Keestash\Exception\User\UserNotFoundException;
use KSA\Settings\Event\Organization\UserChangedEvent;
use KSA\Settings\Exception\SettingsException;
use KSA\Settings\Repository\IOrganizationRepository;
use KSA\Settings\Repository\IOrganizationUserRepository;
use KSP\Api\IResponse;
use KSP\Core\Repository\User\IUserRepository;
use KSP\Core\Service\Event\IEventService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class User implements RequestHandlerInterface {

    public const MODE_ADD    = 'add.mode';
    public const MODE_REMOVE = 'remove.mode';

    public function __construct(private readonly IOrganizationRepository       $organizationRepository, private readonly IOrganizationUserRepository $organizationUserRepository, private readonly IUserRepository             $userRepository, private readonly IEventService               $eventManager)
    {
    }

    #[\Override]
    public function handle(ServerRequestInterface $request): ResponseInterface {
        $parameters     = (array) $request->getParsedBody();
        $mode           = (string) ($parameters["mode"] ?? '');
        $organizationId = (int) ($parameters["organization_id"] ?? -1);
        $userId         = (string) ($parameters["user_id"] ?? -1);

        if ($organizationId < 1) {
            return new JsonResponse([], IResponse::BAD_REQUEST);
        }
        if ($userId < 1) {
            return new JsonResponse([], IResponse::BAD_REQUEST);
        }

        $organization = $this->organizationRepository->get($organizationId);
        try {
            $user = $this->userRepository->getUserById($userId);
        } catch (UserNotFoundException) {
            return new JsonResponse([], IResponse::NOT_FOUND);
        }

        if (null === $organization) {
            return new JsonResponse([], IResponse::NOT_FOUND);
        }

        switch ($mode) {
            case User::MODE_ADD:

                if (true === $organization->hasUser($user)) {
                    throw new SettingsException('user still in organization');
                }

                $this->organizationUserRepository->insert($user, $organization);

                break;
            case User::MODE_REMOVE;
                $this->organizationUserRepository->remove($user, $organization);

                break;
            default:
                return new JsonResponse([], IResponse::BAD_REQUEST);
        }

        $this->eventManager
            ->execute(
                new UserChangedEvent($organization)
            );

        return new JsonResponse(
            []
            , IResponse::OK
        );
    }

}