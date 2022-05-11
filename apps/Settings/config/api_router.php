<?php
declare(strict_types=1);

use KSA\Settings\Api\Organization\Activate;
use KSA\Settings\Api\Organization\Add;
use KSA\Settings\Api\Organization\Get;
use KSA\Settings\Api\Organization\ListAll;
use KSA\Settings\Api\Organization\Update;
use KSA\Settings\Api\Organization\User;
use KSA\Settings\Api\User\GetAll;
use KSA\Settings\Api\User\UpdateProfileImage;
use KSA\Settings\Api\User\UserAdd;
use KSA\Settings\Api\User\UserEdit;
use KSA\Settings\Api\User\UserLock;
use KSA\Settings\Api\User\UserRemove;
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
        [
            'path'       => '/users/edit[/]',
            'middleware' => UserEdit::class,
            'method'     => IVerb::POST,
            'name'       => UserEdit::class
        ],
        [
            'path'       => '/users/add[/]',
            'middleware' => UserAdd::class,
            'method'     => IVerb::POST,
            'name'       => UserAdd::class
        ],
        [
            'path'       => '/users/all[/]',
            'middleware' => GetAll::class,
            'method'     => IVerb::GET,
            'name'       => GetAll::class
        ],
        [
            'path'       => '/users/remove[/]',
            'middleware' => UserRemove::class,
            'method'     => IVerb::POST,
            'name'       => UserRemove::class
        ],
        [
            'path'       => '/users/lock[/]',
            'middleware' => UserLock::class,
            'method'     => IVerb::POST,
            'name'       => UserLock::class
        ],
        [
            'path'         => '/users/get/:userHash[/]'
            , 'middleware' => \KSA\Settings\Api\User\Get::class
            , 'method'     => IVerb::GET
            , 'name'       => \KSA\Settings\Api\User\Get::class
        ],
        [
            'path'         => '/users/profile_image/update[/]'
            , 'middleware' => UpdateProfileImage::class
            , 'method'     => IVerb::POST
            , 'name'       => UpdateProfileImage::class
        ],
    ],
];