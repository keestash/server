<?php
declare(strict_types=1);

use Keestash\ConfigProvider as CoreConfigProvider;
use KSA\Settings\Api\Organization\Activate;
use KSA\Settings\Api\Organization\Add;
use KSA\Settings\Api\Organization\Get;
use KSA\Settings\Api\Organization\ListAll;
use KSA\Settings\Api\Organization\Update;
use KSA\Settings\Api\Organization\User;
use KSA\Settings\Api\User\Get as UserGetByHash;
use KSA\Settings\Api\User\GetAll;
use KSA\Settings\Api\User\UpdateProfileImage;
use KSA\Settings\Api\User\UserAdd;
use KSA\Settings\Api\User\UserEdit;
use KSA\Settings\Api\User\UserLock;
use KSA\Settings\Api\User\UserRemove;
use KSA\Settings\ConfigProvider;
use KSP\Api\IRoute;
use KSP\Api\IVerb;

return [
    CoreConfigProvider::ROUTES => [
        [
            IRoute::PATH         => ConfigProvider::ORGANIZATION_BY_ID
            , IRoute::MIDDLEWARE => Get::class
            , IRoute::METHOD     => IVerb::GET
            , IRoute::NAME       => Get::class
        ],
        [
            IRoute::PATH         => ConfigProvider::ORGANIZATION_ACTIVATE
            , IRoute::MIDDLEWARE => Activate::class
            , IRoute::METHOD     => IVerb::POST
            , IRoute::NAME       => Activate::class
        ],
        [
            IRoute::PATH         => ConfigProvider::ORGANIZATION_ADD
            , IRoute::MIDDLEWARE => Add::class
            , IRoute::METHOD     => IVerb::POST
            , IRoute::NAME       => Add::class
        ],
        [
            IRoute::PATH         => ConfigProvider::ORGANIZATION_LIST_ALL
            , IRoute::MIDDLEWARE => ListAll::class
            , IRoute::METHOD     => IVerb::GET
            , IRoute::NAME       => ListAll::class
        ],
        [
            IRoute::PATH         => ConfigProvider::ORGANIZATION_UPDATE
            , IRoute::MIDDLEWARE => Update::class
            , IRoute::METHOD     => IVerb::POST
            , IRoute::NAME       => Update::class
        ],
        [
            IRoute::PATH         => ConfigProvider::ORGANIZATION_USER_CHANGE
            , IRoute::MIDDLEWARE => User::class
            , IRoute::METHOD     => IVerb::POST
            , IRoute::NAME       => User::class
        ],
        [
            IRoute::PATH       => ConfigProvider::USER_EDIT,
            IRoute::MIDDLEWARE => UserEdit::class,
            IRoute::METHOD     => IVerb::POST,
            IRoute::NAME       => UserEdit::class
        ],
        [
            IRoute::PATH       => ConfigProvider::USER_ADD,
            IRoute::MIDDLEWARE => UserAdd::class,
            IRoute::METHOD     => IVerb::POST,
            IRoute::NAME       => UserAdd::class
        ],
        [
            IRoute::PATH       => ConfigProvider::USER_GET_ALL,
            IRoute::MIDDLEWARE => GetAll::class,
            IRoute::METHOD     => IVerb::GET,
            IRoute::NAME       => GetAll::class
        ],
        [
            IRoute::PATH       => ConfigProvider::USER_REMOVE,
            IRoute::MIDDLEWARE => UserRemove::class,
            IRoute::METHOD     => IVerb::POST,
            IRoute::NAME       => UserRemove::class
        ],
        [
            IRoute::PATH       => ConfigProvider::USER_LOCK,
            IRoute::MIDDLEWARE => UserLock::class,
            IRoute::METHOD     => IVerb::POST,
            IRoute::NAME       => UserLock::class
        ],
        [
            IRoute::PATH         => ConfigProvider::USER_GET_HASH
            , IRoute::MIDDLEWARE => UserGetByHash::class
            , IRoute::METHOD     => IVerb::GET
            , IRoute::NAME       => UserGetByHash::class
        ],
        [
            IRoute::PATH         => ConfigProvider::USER_PROFILE_IMAGE_UPDATE
            , IRoute::MIDDLEWARE => UpdateProfileImage::class
            , IRoute::METHOD     => IVerb::POST
            , IRoute::NAME       => UpdateProfileImage::class
        ],
    ],
];