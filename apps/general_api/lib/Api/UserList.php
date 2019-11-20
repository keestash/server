<?php
declare(strict_types=1);
/**
 * Keestash
 *
 * Copyright (C) <2019> <Dogan Ucar>
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

namespace KSA\general_api\lib\Api;

use Keestash\Api\AbstractApi;
use Keestash\Core\Manager\AssetManager\AssetManager;
use Keestash\Core\Permission\PermissionFactory;
use KSP\Api\IResponse;
use KSP\Core\DTO\IUser;
use KSP\Core\Manager\AssetManager\IAssetManager;
use KSP\Core\Repository\User\IUserRepository;
use KSP\L10N\IL10N;

class UserList extends AbstractApi {

    public const USER_TYPE_ALL  = "all";
    public const USER_TYPE_SEEN = "seen";

    private $parameters     = null;
    private $userRepository = null;
    /** @var IAssetManager|null|AssetManager $assetManager */
    private $assetManager = null;

    public function __construct(
        IL10N $l10n
        , IUserRepository $userRepository
        , IAssetManager $assetManager
    ) {
        parent::__construct($l10n);

        $this->userRepository = $userRepository;
        $this->assetManager   = $assetManager;
    }

    public function onCreate(array $parameters): void {
        $this->parameters = $parameters;

        parent::setPermission(
            PermissionFactory::getDefaultPermission()
        );
    }

    public function create(): void {
        $type         = $this->parameters['type'];
        $all          = $this->userRepository->getAll();
        $pictureTable = [];

        /** @var IUser $user */
        foreach ($all as $key => $user) {
            if ($type === UserList::USER_TYPE_SEEN && null === $user->getLastLogin()) {
                $all->remove($key);
            }

            $picture = $this->assetManager->getProfilePicture($user);

            if (null === $picture) {
                $picture = $this->assetManager->getDefaultImage();
            }
            $picture = $this->assetManager->uriToBase64($picture);

            $pictureTable[$user->getId()] = $picture;
        }


        $response = parent::createResponse(
            IResponse::RESPONSE_CODE_OK
            , [
                "user_list"  => $all
                , "pictures" => $pictureTable
            ]
        );

        parent::setResponse($response);
    }

    public function afterCreate(): void {

    }

}