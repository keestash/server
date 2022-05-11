<?php
declare(strict_types=1);

use Keestash\ConfigProvider as CoreConfigProvider;
use KSA\Settings\ConfigProvider;
use KSA\Settings\Controller\Organization\Detail;
use KSA\Settings\Controller\Controller;

return [
    CoreConfigProvider::ROUTES                 => [
        [
            'path'         => ConfigProvider::SETTINGS
            , 'middleware' => Controller::class
            , 'name'       => Controller::class
        ],
        [
            'path'         => ConfigProvider::ORGANIZATION_SINGLE
            , 'middleware' => Detail::class
            , 'name'       => Detail::class
        ],
    ],
    CoreConfigProvider::WEB_ROUTER_SCRIPTS     => [
        ConfigProvider::SETTINGS            => 'settings',
        ConfigProvider::ORGANIZATION_SINGLE => 'organization_detail'
    ],
    CoreConfigProvider::WEB_ROUTER_STYLESHEETS => [
        ConfigProvider::SETTINGS              => 'settings'
        , ConfigProvider::ORGANIZATION_SINGLE => 'detail'
    ],
    CoreConfigProvider::SETTINGS               => [
        ConfigProvider::SETTINGS => [
            CoreConfigProvider::SETTINGS_NAME    => 'Settings'
            , 'faClass'                          => "fas fa-sliders-h"
            , CoreConfigProvider::SETTINGS_ORDER => 1
        ]
    ]
];