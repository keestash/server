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
use Keestash\Api\AbstractApi;
use KSA\GeneralApi\Exception\GeneralApiException;
use KSA\GeneralApi\Repository\IOrganizationRepository;
use KSA\GeneralApi\Repository\IOrganizationUserRepository;
use KSP\Api\IResponse;
use KSP\Core\DTO\Token\IToken;
use KSP\Core\Repository\User\IUserRepository;
use KSP\L10N\IL10N;

class User extends AbstractApi {

    public const MODE_ADD    = 'add.mode';
    public const MODE_REMOVE = 'remove.mode';

    private IOrganizationRepository     $organizationRepository;
    private IOrganizationUserRepository $organizationUserRepository;
    private IUserRepository             $userRepository;

    public function __construct(
        IL10N $l10n
        , IOrganizationRepository $organizationRepository
        , IOrganizationUserRepository $organizationUserRepository
        , IUserRepository $userRepository
        , ?IToken $token = null
    ) {
        parent::__construct($l10n, $token);
        $this->organizationUserRepository = $organizationUserRepository;
        $this->organizationRepository     = $organizationRepository;
        $this->userRepository             = $userRepository;
    }

    public function onCreate(array $parameters): void {

    }

    public function create(): void {
        $mode           = $this->getParameter("mode");
        $organizationId = $this->getParameter("organization_id");
        $userId         = $this->getParameter("user_id");

        if (null === $organizationId || "" === $organizationId || false === is_numeric($organizationId)) {
            throw new GeneralApiException('no organization');
        }
        if (null === $userId || "" === $userId || false === is_numeric($userId)) {
            throw new GeneralApiException('no user');
        }

        $organization = $this->organizationRepository->get((int) $organizationId);
        $user         = $this->userRepository->getUserById($userId);

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

        $this->createAndSetResponse(
            IResponse::RESPONSE_CODE_OK
            , [
                'messages' => 'ok'
            ]
        );
    }

    public function afterCreate(): void {

    }

}