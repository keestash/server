<?php
declare(strict_types=1);

/**
 * Keestash
 *
 * Copyright (C) <2022> <Dogan Ucar>
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

use Keestash\ConfigProvider;
use KSA\Register\Api\Configuration\Configuration;
use KSA\Register\Api\User\Add;
use KSA\Register\Api\User\Confirm;
use KSA\Register\Api\User\ResetPassword;
use KSA\Register\Api\User\ResetPasswordConfirm;
use KSA\Register\Api\User\ResetPasswordRetrieve;
use KSA\Register\Command\ActivateUser;
use KSA\Register\Command\CreateUser;
use KSA\Register\Command\DeleteUser;
use KSA\Register\Event\Listener\ResetPasswordSendEmailListener;
use KSA\Register\Event\Listener\ResetPasswordSendEmailListenerFactory;
use KSA\Register\Event\Listener\UserRegisteredEventListener;
use KSA\Register\Factory\Api\AddFactory;
use KSA\Register\Factory\Api\Configuration\ConfigurationFactory;
use KSA\Register\Factory\Api\ConfirmFactory;
use KSA\Register\Factory\Api\ResetPasswordConfirmFactory;
use KSA\Register\Factory\Api\ResetPasswordFactory;
use KSA\Register\Factory\Api\ResetPasswordRetrieveFactory;
use KSA\Register\Factory\Command\ActivateUserFactory;
use KSA\Register\Factory\Command\CreateUserFactory;
use KSA\Register\Factory\Command\DeleteUserFactory;
use KSA\Register\Factory\Event\Listener\UserRegisteredEventListenerFactory;
use KSA\Register\Factory\Middleware\RegisterEnabledMiddlewareFactory;
use KSA\Register\Middleware\RegisterEnabledMiddleware;

return [
    ConfigProvider::FACTORIES => [
        // api
        Add::class                              => AddFactory::class
        , Configuration::class                  => ConfigurationFactory::class
        , Confirm::class                        => ConfirmFactory::class
        , ResetPassword::class                  => ResetPasswordFactory::class
        , ResetPasswordConfirm::class           => ResetPasswordConfirmFactory::class
        , ResetPasswordRetrieve::class          => ResetPasswordRetrieveFactory::class

        // command
        , CreateUser::class                     => CreateUserFactory::class
        , DeleteUser::class                     => DeleteUserFactory::class
        , ActivateUser::class                   => ActivateUserFactory::class

        // event
        // ---- listener
        , UserRegisteredEventListener::class    => UserRegisteredEventListenerFactory::class
        , ResetPasswordSendEmailListener::class => ResetPasswordSendEmailListenerFactory::class

        // middleware
        , RegisterEnabledMiddleware::class      => RegisterEnabledMiddlewareFactory::class
    ]
];