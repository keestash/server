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

namespace Keestash\Core\Service\HTTP;

use DateTime;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Keestash\Core\DTO\Http\JWT\Audience;
use Keestash\Core\Repository\Instance\InstanceDB;
use KSP\Core\DTO\Http\JWT\IAudience;
use KSP\Core\Service\HTTP\IHTTPService;
use KSP\Core\Service\HTTP\IJWTService;

class JWTService implements IJWTService {

    public function __construct(private readonly IHTTPService $httpService, private readonly InstanceDB $instanceDB)
    {
    }

    #[\Override]
    public function getJWT(IAudience $audience): string {
        $payLoad = [
            'iss'   => $this->httpService->getBaseURL(false, false)
            , 'aud' => [
                'type'    => $audience->getType()
                , 'value' => $audience->getValue()
            ]
            , 'iat' => (new DateTime())->getTimestamp()
            , 'nbf' => (new DateTime())->getTimestamp() - 1
        ];

        return JWT::encode(
            $payLoad
            , (string) $this->instanceDB->getOption(InstanceDB::OPTION_NAME_INSTANCE_HASH)
            , 'HS256'
        );

    }

    #[\Override]
    public function decodeJwt(string $jwt): IAudience {
        $object = JWT::decode(
            $jwt
            , new Key(
                (string) $this->instanceDB->getOption(InstanceDB::OPTION_NAME_INSTANCE_HASH)
                , 'HS256'
            )
        );
        return new Audience(
            (string) $object->aud->type
            , (string) $object->aud->value
        );
    }

}
