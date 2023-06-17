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
use Keestash\Middleware\RateLimiterMiddleware;
use KSP\Core\Backend\IBackend;
use KSP\Core\Backend\SQLBackend\ISQLBackend;
use KSP\Core\Service\Config\IIniConfigService;
use KSP\Core\Service\Core\Locale\ILocaleService;
use KSP\Core\Service\Email\IEmailService;
use KSP\Core\Service\Encryption\Credential\ICredentialService;
use KSP\Core\Service\Event\IEventService;
use KSP\Core\Service\File\Upload\IFileService;
use KSP\Core\Service\HTTP\IHTTPService;
use KSP\Core\Service\LDAP\ILDAPService;
use KSP\Core\Service\Phinx\IMigrator;
use KST\Service\Core\Backend\SQLiteBackend;
use KST\Service\Core\Cache\RedisService;
use KST\Service\Core\Manager\EventManager\EventService;
use KST\Service\Core\Service\Config\IniConfigService;
use KST\Service\Core\Service\Core\Locale\LocaleService;
use KST\Service\Core\Service\Email\EmailService;
use KST\Service\Core\Service\Encryption\Credential\CredentialService;
use KST\Service\Core\Service\File\Upload\FileService;
use KST\Service\Core\Service\HTTP\HTTPService;
use KST\Service\Core\Service\LDAP\LDAPService;
use KST\Service\Core\Service\Phinx\Migrator;
use KST\Service\Middleware\DeactivatedRouteMiddleware;
use KST\Service\Middleware\RateLimiterMiddleware as TestRateLimiter;

return [
    IMigrator::class                                         => Migrator::class
    , IHTTPService::class                                    => HTTPService::class
    , IEventService::class                                   => EventService::class
    , ILocaleService::class                                  => LocaleService::class
    , RealRedisService::class                                => RedisService::class
    , IIniConfigService::class                               => IniConfigService::class
    , IEmailService::class                                   => EmailService::class
    , ICredentialService::class                              => CredentialService::class
    , IFileService::class                                    => FileService::class
    , RateLimiterMiddleware::class                           => TestRateLimiter::class
    , \Keestash\Middleware\DeactivatedRouteMiddleware::class => DeactivatedRouteMiddleware::class
    , ISQLBackend::class                                     => SQLiteBackend::class
    , IBackend::class                                        => ISQLBackend::class
    , ILDAPService::class                                    => LDAPService::class
];