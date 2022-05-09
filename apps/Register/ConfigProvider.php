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
use KSA\Register\Command\CreateUser;
use KSA\Register\Event\EmailAfterRegistration;

final class ConfigProvider {

    public const REGISTER              = '/register[/]';
    public const REGISTER_ADD          = '/register/add[/]';
    public const PASSWORD_REQUIREMENTS = '/password_requirements[/]';
    public const APP_ID                = 'register';

    public function __invoke(): array {
        return [
            CoreConfigProvider::APP_LIST     => [
                ConfigProvider::APP_ID => [
                    CoreConfigProvider::APP_ORDER      => 9,
                    CoreConfigProvider::APP_NAME       => 'Register',
                    CoreConfigProvider::APP_BASE_ROUTE => ConfigProvider::REGISTER,
                    CoreConfigProvider::APP_VERSION    => 1,
                ],
            ],
            CoreConfigProvider::DEPENDENCIES => require __DIR__ . '/config/dependencies.php',
            CoreConfigProvider::WEB_ROUTER   => require __DIR__ . '/config/web_router.php',
            CoreConfigProvider::API_ROUTER   => require __DIR__ . '/config/api_router.php',
            CoreConfigProvider::EVENTS       => [
                UserCreatedEvent::class => [
                    EmailAfterRegistration::class
                ]
            ],
            CoreConfigProvider::COMMANDS     => [
                CreateUser::class
            ],
            'templates'                      => [
                'paths' => [
                    'register' => [__DIR__ . '/template']
                ]
            ]
        ];
    }

}