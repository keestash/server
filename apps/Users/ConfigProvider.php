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

namespace KSA\Users;

use Keestash\Core\Service\User\Event\UserStateDeleteEvent;
use KSA\Users\Api\File\ProfilePicture;
use KSA\Users\Api\User\GetAll;
use KSA\Users\Api\User\UserAdd;
use KSA\Users\Api\User\UserEdit;
use KSA\Users\Api\User\UserLock;
use KSA\Users\Api\User\UserRemove;
use KSA\Users\BackgroundJob\UserDeleteTask;
use KSA\Users\Controller\UsersController;
use KSA\Users\Event\Listener\PostStateChange;
use KSA\Users\Factory\Api\File\ProfilePictureFactory;
use KSA\Users\Factory\Api\User\GetAllFactory;
use KSA\Users\Factory\Api\User\UserAddFactory;
use KSA\Users\Factory\Api\User\UserEditFactory;
use KSA\Users\Factory\Api\User\UserLockFactory;
use KSA\Users\Factory\Api\User\UserRemoveFactory;
use KSA\Users\Factory\BackgroundJob\UserDeleteTaskFactory;
use KSA\Users\Factory\Controller\UsersControllerFactory;
use KSA\Users\Factory\Event\Listener\PostStateChangeFactory;
use KSP\App\IApp;
use KSP\Core\DTO\Http\IVerb;

final class ConfigProvider {

    public const USERS = "/users[/]";

    // TODO register background jobs
    //"background_jobs": {
    //"KSA\\Users\\BackgroundJob\\UserDeleteTask": {
    //"type": "regular.type.job",
    //"interval": 68400
    //}
    //}

    public function __invoke(): array {
        return [
            'dependencies'                   => [
                'factories' => [
                    // api
                    ProfilePicture::class  => ProfilePictureFactory::class,
                    GetAll::class          => GetAllFactory::class,
                    UserAdd::class         => UserAddFactory::class,
                    UserEdit::class        => UserEditFactory::class,
                    UserLock::class        => UserLockFactory::class,
                    UserRemove::class      => UserRemoveFactory::class,

                    // background job
                    UserDeleteTask::class  => UserDeleteTaskFactory::class,

                    // listener
                    PostStateChange::class => PostStateChangeFactory::class,

                    // controller
                    UsersController::class => UsersControllerFactory::class
                ]
            ],
            IApp::CONFIG_PROVIDER_API_ROUTER => [
                IApp::CONFIG_PROVIDER_ROUTES => [
                    [
                        'path'       => '/users/add[/]',
                        'middleware' => UserAdd::class,
                        'method'     => IVerb::POST,
                        'name'       => UserAdd::class
                    ],
                    [
                        'path'       => '/users/edit[/]',
                        'middleware' => UserEdit::class,
                        'method'     => IVerb::POST,
                        'name'       => UserEdit::class
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
                        'path'       => '/users/profile_pictures/:token/:user_hash/:targetId[/]',
                        'middleware' => ProfilePicture::class,
                        'method'     => IVerb::GET,
                        'name'       => ProfilePicture::class
                    ],
                ]
            ],
            IApp::CONFIG_PROVIDER_WEB_ROUTER => [
                IApp::CONFIG_PROVIDER_ROUTES                 => [
                    [
                        'path'         => ConfigProvider::USERS
                        , 'middleware' => UsersController::class
                        , 'name'       => UsersController::class
                    ],
                ],
                IApp::CONFIG_PROVIDER_WEB_ROUTER_STYLESHEETS => [
                    ConfigProvider::USERS => 'users'
                ],
                IApp::CONFIG_PROVIDER_WEB_ROUTER_SCRIPTS     => [
                    ConfigProvider::USERS => 'users'
                ],
                IApp::CONFIG_PROVIDER_SETTINGS               => [
                    ConfigProvider::USERS => [
                        'name'      => 'users'
                        , 'faClass' => 'fas fa-user-circle'
                        , 'order'   => 1
                    ]
                ]
            ],
            IApp::CONFIG_PROVIDER_EVENTS     => [
                UserStateDeleteEvent::class => PostStateChange::class
            ],
            'templates'                      => [
                'paths' => [
                    'users' => [__DIR__ . '/template']
                ]
            ]
        ];
    }

}