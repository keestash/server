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

namespace KSA\Settings;

use Keestash\ConfigProvider as CoreConfigProvider;
use Keestash\Core\Service\User\Event\UserStateDeleteEvent;
use KSA\Settings\Command\UpdatePassword;
use KSA\Settings\Event\Listener\OrganizationAddedEventListener;
use KSA\Settings\Event\Listener\PostStateChange;
use KSA\Settings\Event\Organization\OrganizationAddedEvent;
use KSA\Settings\Event\Organization\UserChangedEvent;

// TODO register background jobs
//"background_jobs": {
//"KSA\\Users\\BackgroundJob\\UserDeleteTask": {
//"type": "regular.type.job",
//"interval": 68400
//}
//}

final class ConfigProvider {

    public const SETTINGS              = "/settings[/]";
    public const APP_ID                = 'settings';
    public const ORGANIZATION_LIST_ALL = '/organizations/all/[:includeInactive/][:userHash/]';

    public const ORGANIZATION_SINGLE = "/organizations/:id[/]";

    public function __invoke(): array {
        return [
            CoreConfigProvider::DEPENDENCIES => require __DIR__ . '/config/dependencies.php'
            , CoreConfigProvider::API_ROUTER => require __DIR__ . '/config/api_router.php'
            , CoreConfigProvider::WEB_ROUTER => require __DIR__ . '/config/web_router.php'
            , CoreConfigProvider::APP_LIST   => [
                ConfigProvider::APP_ID => [
                    CoreConfigProvider::APP_ORDER      => 10,
                    CoreConfigProvider::APP_NAME       => 'Settings',
                    CoreConfigProvider::APP_BASE_ROUTE => ConfigProvider::SETTINGS,
                    CoreConfigProvider::APP_VERSION    => 1,
                ],
            ]
            , CoreConfigProvider::EVENTS     => [
                OrganizationAddedEvent::class => [
                    OrganizationAddedEventListener::class
                ]
                , UserStateDeleteEvent::class => [
                    PostStateChange::class
                ]
            ]
            , CoreConfigProvider::COMMANDS   => [
                UpdatePassword::class
            ]
            , 'templates'                    => [
                'paths' => [
                    'settings' => [__DIR__ . '/template/']
                ]
            ]
        ];
    }

}