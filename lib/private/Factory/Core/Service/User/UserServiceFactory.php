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

namespace Keestash\Factory\Core\Service\User;

use doganoo\DI\DateTime\IDateTimeService;
use doganoo\DI\Object\String\IStringService;
use Keestash\Core\Service\User\UserService;
use Keestash\Legacy\Legacy;
use KSP\Core\Service\Core\Language\ILanguageService;
use KSP\Core\Service\Core\Locale\ILocaleService;
use KSP\Core\Service\User\IUserService;
use KSP\Core\Service\User\Repository\IUserRepositoryService;
use Laminas\Config\Config;
use Laminas\I18n\Validator\PhoneNumber;
use Laminas\Validator\EmailAddress;
use Laminas\Validator\Uri;
use Psr\Container\ContainerInterface;

class UserServiceFactory {

    public function __invoke(ContainerInterface $container): IUserService {
        return new UserService(
            $container->get(Legacy::class)
            , $container->get(IDateTimeService::class)
            , $container->get(IStringService::class)
            , $container->get(IUserRepositoryService::class)
            , $container->get(EmailAddress::class)
            , $container->get(PhoneNumber::class)
            , $container->get(Uri::class)
            , $container->get(ILocaleService::class)
            , $container->get(ILanguageService::class)
            , $container->get(Config::class)
        );
    }

}