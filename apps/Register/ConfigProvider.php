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

namespace KSA\Register;

use Keestash\ConfigProvider as CoreConfigProvider;
use Keestash\Core\Service\User\Event\UserCreatedEvent;
use KSA\Register\Api\User\Add;
use KSA\Register\Api\User\Exists;
use KSA\Register\Api\User\MailExists;
use KSA\Register\Command\CreateUser;
use KSA\Register\Controller\Controller;
use KSA\Register\Event\EmailAfterRegistration;
use KSA\Register\Factory\Api\AddFactory;
use KSA\Register\Factory\Api\ExistsFactory;
use KSA\Register\Factory\Api\MailExistsFactory;
use KSA\Register\Factory\Command\CreateUserFactory;
use KSA\Register\Factory\Controller\ControllerFactory;
use KSA\Register\Factory\Event\Listener\CreateKeyFactory;
use KSA\Register\Factory\Event\Listener\EmailAfterRegistrationListenerFactory;
use KSP\Core\DTO\Http\IVerb;

final class ConfigProvider {

    public const REGISTER     = '/register[/]';
    public const REGISTER_ADD = '/register/add[/]';
    public const APP_ID       = 'register';

    public function __invoke(): array {
        return [
            CoreConfigProvider::APP_LIST   => [
                ConfigProvider::APP_ID => [
                    CoreConfigProvider::APP_ORDER      => 9,
                    CoreConfigProvider::APP_NAME       => 'About',
                    CoreConfigProvider::APP_BASE_ROUTE => ConfigProvider::REGISTER,
                    CoreConfigProvider::APP_VERSION    => 1,
                ],
            ],
            'dependencies'                 => [
                'factories' => [
                    Exists::class                 => ExistsFactory::class,
                    Add::class                    => AddFactory::class,
                    MailExists::class             => MailExistsFactory::class,
                    EmailAfterRegistration::class => EmailAfterRegistrationListenerFactory::class,
                    CreateUser::class             => CreateUserFactory::class,

                    // controller
                    Controller::class             => ControllerFactory::class,
                ]
            ],
            CoreConfigProvider::WEB_ROUTER => [
                CoreConfigProvider::ROUTES                 => [
                    [
                        'path'         => ConfigProvider::REGISTER
                        , 'middleware' => Controller::class
                        , 'name'       => Controller::class
                    ],
                ],
                CoreConfigProvider::PUBLIC_ROUTES          => [
                    ConfigProvider::REGISTER
                ],
                CoreConfigProvider::WEB_ROUTER_SCRIPTS     => [
                    ConfigProvider::REGISTER => 'register'
                ],
                CoreConfigProvider::WEB_ROUTER_STYLESHEETS => [
                    ConfigProvider::REGISTER => 'register'
                ]
            ],
            CoreConfigProvider::API_ROUTER => [
                CoreConfigProvider::PUBLIC_ROUTES => [
                    ConfigProvider::REGISTER_ADD,
                ],
                CoreConfigProvider::ROUTES        => [
                    [
                        'path'       => ConfigProvider::REGISTER_ADD,
                        'middleware' => Add::class,
                        'method'     => IVerb::POST,
                        'name'       => Add::class
                    ],
                    [
                        'path'       => '/user/mail/exists/:address[/]',
                        'middleware' => MailExists::class,
                        'method'     => IVerb::GET,
                        'name'       => MailExists::class
                    ],
                    [
                        'path'       => '/user/exists/:userName[/]',
                        'middleware' => Exists::class,
                        'method'     => IVerb::GET,
                        'name'       => Exists::class
                    ],
                ]
            ],
            CoreConfigProvider::EVENTS     => [
                UserCreatedEvent::class => [
                    EmailAfterRegistration::class
                ]
            ],
            CoreConfigProvider::COMMANDS   => [
                CreateUser::class
            ],
            'templates'                    => [
                'paths' => [
                    'register' => [__DIR__ . '/template']
                ]
            ]
        ];
    }

}