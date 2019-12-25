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

namespace Keestash\Core\Service\File\PublicFile;

use Keestash;
use Keestash\Core\DTO\URI\URL\URL;
use KSP\Core\DTO\IToken;
use KSP\Core\DTO\IUser;
use KSP\Core\DTO\URI\URL\IUniformResourceLocator;

/**
 * Class PublicFileService
 * @package Keestash\Core\Service\File\PublicFile
 */
class PublicFileService {

    public function getPublicProfilePictureURL(IUser $user, ?IToken $token = null): ?IUniformResourceLocator {
        if (null === $token) return null;
        $baseURL    = Keestash::getBaseURL(true, false);
        $identifier = "$baseURL/users/profile_pictures/{$token->getValue()}/{$user->getHash()}/";

        $url = new URL();
        $url->setIdentifier($identifier);
        return $url;
    }

    public function getPublicIconURL(string $extension): ?IUniformResourceLocator {
        $baseURL    = Keestash::getBaseURL(true, false);
        $identifier = "$baseURL/icon/file/get/$extension/";

        $url = new URL();
        $url->setIdentifier($identifier);
        return $url;
    }

}