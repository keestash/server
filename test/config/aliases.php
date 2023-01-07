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

use Keestash\Core\Service\Cache\RedisService as RealRedisService;
use Keestash\Core\System\Installation\App\LockHandler as CoreAppLockHandler;
use Keestash\Core\System\Installation\Instance\LockHandler as CoreInstanceLockHandler;
use KSP\Core\Service\Config\IIniConfigService;
use KSP\Core\Service\Core\Locale\ILocaleService;
use KSP\Core\Service\Email\IEmailService;
use KSP\Core\Service\Event\IEventService;
use KSP\Core\Service\HTTP\IHTTPService;
use KSP\Core\Service\Phinx\IMigrator;
use KST\Service\Core\Cache\RedisService;
use KST\Service\Core\Manager\EventManager\EventService;
use KST\Service\Core\Service\Config\IniConfigService;
use KST\Service\Core\Service\Core\Locale\LocaleService;
use KST\Service\Core\Service\Email\EmailService;
use KST\Service\Core\Service\HTTP\HTTPService;
use KST\Service\Core\Service\Phinx\Migrator;
use KST\Service\Core\System\Installation\App\LockHandler as TestAppLockHandler;
use KST\Service\Core\System\Installation\Instance\LockHandler as TestInstanceLockHandler;

return [
    IMigrator::class                 => Migrator::class
    , IHTTPService::class            => HTTPService::class
    , IEventService::class           => EventService::class
    , ILocaleService::class          => LocaleService::class
    , RealRedisService::class        => RedisService::class
    , CoreAppLockHandler::class      => TestAppLockHandler::class
    , CoreInstanceLockHandler::class => TestInstanceLockHandler::class
    , IIniConfigService::class       => IniConfigService::class
    , IEmailService::class           => EmailService::class
];