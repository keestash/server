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

namespace KSA\Login\Api;

use Keestash\Api\Response\JsonResponse;
use Keestash\Core\Repository\Instance\InstanceDB;
use KSP\Api\IResponse;
use KSP\Core\DTO\Encryption\Credential\Key\IKey;
use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

#[OA\Get(
    path: '/app/configuration',
    operationId: 'appConfiguration',
    summary: 'Retrieve application configuration flags',
    tags: ['Authentication'],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Configuration data',
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'demo', type: 'string', nullable: true),
                    new OA\Property(property: 'demoMode', type: 'boolean'),
                    new OA\Property(property: 'registerEnabled', type: 'boolean'),
                    new OA\Property(property: 'forgotPasswordEnabled', type: 'boolean'),
                ]
            )
        ),
    ]
)]
readonly final class Configuration implements RequestHandlerInterface {

    public function __construct(private InstanceDB $instanceDB) {

    }

    #[\Override]
    public function handle(ServerRequestInterface $request): ResponseInterface {
        $isDemoMode = $this->instanceDB->getOption("demo") === "true";
        $demo       = $isDemoMode
            ? md5(uniqid())
            : null;

        return new JsonResponse(
            [
                "demo"                    => $demo
                , "demoMode"              => $isDemoMode
                // TODO check here for being enabled once apps page works!
                , "registerEnabled"       => true && !$isDemoMode
                , "forgotPasswordEnabled" => true && !$isDemoMode
                , "kdf"                   => [
                    "algorithm" => "scrypt"
                    , "params"  => [
                        "N"       => 32768
                        , "r"     => 8
                        , "p"     => 1
                        , "dkLen" => 32
                    ]
                    , "salt"    => $this->instanceDB->getOption(InstanceDB::OPTION_NAME_INSTANCE_HASH)
                    , "version" => IKey::KDF_VERSION_SCRYPT_AES_GCM_V1
                ]
            ]
            , IResponse::OK
        );
    }

}
