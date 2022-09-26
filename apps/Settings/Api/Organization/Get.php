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
use doganoo\SimpleRBAC\Service\RBACServiceInterface;
use Keestash\Api\Response\JsonResponse;
use KSA\GeneralApi\Exception\GeneralApiException;
use KSA\Settings\Repository\IOrganizationRepository;
use KSP\Api\IResponse;
use KSP\Core\DTO\RBAC\IPermission;
use KSP\Core\DTO\Token\IToken;
use KSP\Core\DTO\User\IUser;
use KSP\Core\ILogger\ILogger;
use KSP\Core\Repository\User\IUserRepository;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class Get implements RequestHandlerInterface {

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
        $id   = $request->getAttribute('id');
        $user = $request->getAttribute(IToken::class)->getUser();

        if (null === $id || "" === $id || false === is_numeric($id)) {
            throw new GeneralApiException('no id found');
        }

        $organization = $this->organizationRepository->get((int) $id);

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