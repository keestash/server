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
use KSA\Register\Api\MinimumCredential;
use KSA\Register\Api\User\Add;
use KSA\Register\Api\User\Exists;
use KSA\Register\Api\User\MailExists;
use KSA\Register\Command\CreateUser;
use KSA\Register\Command\DeleteUser;
use KSA\Register\Controller\Controller;
use KSA\Register\Event\EmailAfterRegistration;
use KSA\Register\Factory\Api\AddFactory;
use KSA\Register\Factory\Api\Configuration\ConfigurationFactory;
use KSA\Register\Factory\Api\ExistsFactory;
use KSA\Register\Factory\Api\MailExistsFactory;
use KSA\Register\Factory\Api\MinimumCredentialFactory;
use KSA\Register\Factory\Command\CreateUserFactory;
use KSA\Register\Factory\Command\DeleteUserFactory;
use KSA\Register\Factory\Controller\ControllerFactory;
use KSA\Register\Factory\Event\Listener\EmailAfterRegistrationListenerFactory;

return [
    ConfigProvider::FACTORIES => [
        // api
        Exists::class                   => ExistsFactory::class
        , Add::class                    => AddFactory::class
        , MailExists::class             => MailExistsFactory::class
        , EmailAfterRegistration::class => EmailAfterRegistrationListenerFactory::class
        , MinimumCredential::class      => MinimumCredentialFactory::class
        , Configuration::class          => ConfigurationFactory::class

        // controller
        , Controller::class             => ControllerFactory::class,

        // command
        CreateUser::class               => CreateUserFactory::class
        , DeleteUser::class             => DeleteUserFactory::class
    ]
];