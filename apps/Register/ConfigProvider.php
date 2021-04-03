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

use Keestash\Core\Service\User\Event\UserCreatedEvent;
use KSA\Register\Api\User\Add;
use KSA\Register\Api\User\Exists;
use KSA\Register\Api\User\MailExists;
use KSA\Register\Command\CreateUser;
use KSA\Register\Event\EmailAfterRegistration;
use KSA\Register\Factory\Api\AddFactory;
use KSA\Register\Factory\Api\ExistsFactory;
use KSA\Register\Factory\Api\MailExistsFactory;
use KSA\Register\Factory\Command\CreateUserFactory;
use KSA\Register\Factory\Event\Listener\EmailAfterRegistrationListenerFactory;
use KSP\App\IApp;

final class ConfigProvider {

    public function __invoke(): array {
        return [
            'dependencies'               => [
                'factories' => [
                    Exists::class                 => ExistsFactory::class,
                    Add::class                    => AddFactory::class,
                    MailExists::class             => MailExistsFactory::class,
                    EmailAfterRegistration::class => EmailAfterRegistrationListenerFactory::class,
                    CreateUser::class             => CreateUserFactory::class,
                ]
            ],
            IApp::CONFIG_PROVIDER_API_ROUTER => [
                IApp::CONFIG_PROVIDER_PUBLIC_ROUTES => [
                    '/register/add[/]',
                ],
                IApp::CONFIG_PROVIDER_ROUTES        => [
                    [
                        'path'       => '/register/add[/]',
                        'middleware' => Add::class,
                        'method'     => 'post',
                        'name'       => Add::class
                    ],
                    [
                        'path'       => '/user/mail/exists/:address[/]',
                        'middleware' => MailExists::class,
                        'method'     => 'get',
                        'name'       => MailExists::class
                    ],
                    [
                        'path'       => '/user/exists/:userName[/]',
                        'middleware' => Exists::class,
                        'method'     => 'get',
                        'name'       => Exists::class
                    ],
                ]
            ],
            IApp::CONFIG_PROVIDER_EVENTS => [
                UserCreatedEvent::class => EmailAfterRegistration::class
            ],
            IApp::CONFIG_PROVIDER_COMMANDS => [
                CreateUser::class
            ]
        ];
    }

}