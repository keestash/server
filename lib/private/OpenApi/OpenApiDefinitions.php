<?php
declare(strict_types=1);

namespace Keestash\OpenApi;

use OpenApi\Attributes as OA;

#[OA\OpenApi(
    info: new OA\Info(
        version: '1.0.0',
        title: 'Keestash API',
        description: 'REST API for the Keestash password manager',
        license: new OA\License(name: 'AGPL-3.0-or-later')
    ),
    servers: [new OA\Server(url: '/api', description: 'API base path')]
)]
#[OA\SecurityScheme(
    securityScheme: 'tokenAuth',
    type: 'apiKey',
    in: 'header',
    name: 'x-keestash-token',
    description: 'Session token returned in the x-keestash-token response header on login'
)]
#[OA\SecurityScheme(
    securityScheme: 'userAuth',
    type: 'apiKey',
    in: 'header',
    name: 'x-keestash-user',
    description: 'User hash returned in the x-keestash-user response header on login'
)]
class OpenApiDefinitions
{
}
