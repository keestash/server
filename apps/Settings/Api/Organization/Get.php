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
use KSP\Core\DTO\User\IUser;
use KSP\Core\Repository\User\IUserRepository;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class Get implements RequestHandlerInterface {

    public function __construct(
        private readonly IOrganizationRepository $organizationRepository,
        private readonly IUserRepository         $userRepository
    ) {
    }

    #[\Override]
    public function handle(ServerRequestInterface $request): ResponseInterface {
        $id = (int) $request->getAttribute('id', 0);

        if ($id < 1) {
            return new JsonResponse([], IResponse::BAD_REQUEST);
        }

        $organization = $this->organizationRepository->get($id);

        if (null === $organization) {
            return new JsonResponse(
                []
                , IResponse::NOT_FOUND
            );
        }

        $users      = $this->userRepository->getAll();
        $candidates = new ArrayList();

        /** @var IUser $user */
        foreach ($users as $user) {

            if ($user->getId() === IUser::SYSTEM_USER_ID) {
                continue;
            }

            $exists = false;
            /** @var IUser $organizationUser */
            foreach ($organization->getUsers() as $organizationUser) {

                if ($user->getId() === $organizationUser->getId()) {
                    $exists = true;
                    break;
                }
            }
            if (false === $exists) {
                $candidates->add($user);
            }
        }

        return new JsonResponse(
            [
                'organization' => $organization
                , 'users'      => $candidates
            ]
            , IResponse::OK
        );
    }

}
