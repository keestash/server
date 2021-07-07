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

namespace KSA\GeneralApi\Api\Organization;

use doganoo\PHPAlgorithms\Datastructure\Lists\ArrayList\ArrayList;
use Keestash\Api\Response\LegacyResponse;
use KSA\GeneralApi\Event\Organization\UserChangedEvent;
use KSA\GeneralApi\Exception\GeneralApiException;
use KSA\GeneralApi\Repository\IOrganizationRepository;
use KSA\GeneralApi\Repository\IOrganizationUserRepository;
use KSP\Api\IResponse;
use KSP\Core\Manager\EventManager\IEventManager;
use KSP\Core\Repository\User\IUserRepository;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class User implements RequestHandlerInterface {

    public const MODE_ADD    = 'add.mode';
    public const MODE_REMOVE = 'remove.mode';

    private IOrganizationRepository     $organizationRepository;
    private IOrganizationUserRepository $organizationUserRepository;
    private IUserRepository             $userRepository;
    private IEventManager               $eventManager;

    public function __construct(
        IOrganizationRepository $organizationRepository
        , IOrganizationUserRepository $organizationUserRepository
        , IUserRepository $userRepository
        , IEventManager $eventManager
    ) {
        $this->organizationUserRepository = $organizationUserRepository;
        $this->organizationRepository     = $organizationRepository;
        $this->userRepository             = $userRepository;
        $this->eventManager               = $eventManager;
    }


    public function handle(ServerRequestInterface $request): ResponseInterface {
        $parameters     = json_decode((string) $request->getBody(), true);
        $mode           = $parameters["mode"] ?? null;
        $organizationId = $parameters["organization_id"] ?? null;
        $userId         = $parameters["user_id"] ?? null;

        if (null === $organizationId || "" === $organizationId || false === is_numeric($organizationId)) {
            throw new GeneralApiException('no organization');
        }
        if (null === $userId || "" === $userId || false === is_numeric($userId)) {
            throw new GeneralApiException('no user');
        }

        $organization = $this->organizationRepository->get((int) $organizationId);
        $user         = $this->userRepository->getUserById((string) $userId);

        if (null === $organization) {
            throw new GeneralApiException('no organization found');
        }
        if (null === $user) {
            throw new GeneralApiException('no user found');
        }

        $organization->setUsers(new ArrayList());

        switch ($mode) {
            case User::MODE_ADD:
                $organization->addUser($user);
                $this->organizationUserRepository->insert($organization);

                break;
            case User::MODE_REMOVE;
                $this->organizationUserRepository->remove($user, $organization);

                break;
            default:
                throw new GeneralApiException('no mode given');
        }

        $this->eventManager
            ->execute(
                new UserChangedEvent($organization)
            );

        return LegacyResponse::fromData(
            IResponse::RESPONSE_CODE_OK
            , [
                'messages' => 'ok'
            ]
        );
    }

}