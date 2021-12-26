<?php
declare(strict_types=1);

use KSA\Settings\Api\Organization\Activate;
use KSA\Settings\Api\Organization\Add;
use KSA\Settings\Api\Organization\Get;
use KSA\Settings\Api\Organization\ListAll;
use KSA\Settings\Api\Organization\Update;
use KSA\Settings\Api\Organization\User;
use KSA\Settings\ConfigProvider;
use KSP\Api\IVerb;

return [
    \Keestash\ConfigProvider::ROUTES => [
        [
            'path'         => '/organizations/:id[/]'
            , 'middleware' => Get::class
            , 'method'     => IVerb::GET
            , 'name'       => Get::class
        ],
        [
            'path'         => '/organizations/activate[/]'
            , 'middleware' => Activate::class
            , 'method'     => IVerb::POST
            , 'name'       => Activate::class
        ],
        [
            'path'         => '/organizations/add[/]'
            , 'middleware' => Add::class
            , 'method'     => IVerb::POST
            , 'name'       => Add::class
        ],
        [
            'path'         => ConfigProvider::ORGANIZATION_LIST_ALL
            , 'middleware' => ListAll::class
            , 'method'     => IVerb::GET
            , 'name'       => ListAll::class
        ],
        [
            'path'         => '/organizations/update[/]'
            , 'middleware' => Update::class
            , 'method'     => IVerb::POST
            , 'name'       => Update::class
        ],
        [
            'path'         => '/organizations/user/change[/]'
            , 'middleware' => User::class
            , 'method'     => IVerb::POST
            , 'name'       => User::class
        ],
    ],
];