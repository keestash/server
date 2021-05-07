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


use doganoo\DI\DateTime\IDateTimeService;
use doganoo\DIP\DateTime\DateTimeService;
use Keestash\App\Loader\Loader;
use Keestash\Core\Backend\MySQLBackend;
use Keestash\Core\Cache\NullService;
use Keestash\Core\Manager\CookieManager\CookieManager;
use Keestash\Core\Manager\EventManager\EventManager;
use Keestash\Core\Manager\FileManager\FileManager;
use Keestash\Core\Manager\LoggerManager\LoggerManager;
use Keestash\Core\Manager\SessionManager\SessionManager;
use Keestash\Core\Manager\SettingManager\SettingManager;
use Keestash\Core\Repository\ApiLog\ApiLogRepository;
use Keestash\Core\Repository\AppRepository\AppRepository;
use Keestash\Core\Repository\EncryptionKey\Organization\OrganizationKeyRepository;
use Keestash\Core\Repository\EncryptionKey\User\UserKeyRepository;
use Keestash\Core\Repository\File\FileRepository;
use Keestash\Core\Repository\Job\JobRepository;
use Keestash\Core\Repository\Session\SessionRepository;
use Keestash\Core\Repository\Token\TokenRepository;
use Keestash\Core\Repository\User\UserRepository;
use Keestash\Core\Repository\User\UserStateRepository;
use Keestash\Core\Service\App\AppService;
use Keestash\Core\Service\Config\ConfigService;
use Keestash\Core\Service\Controller\AppRenderer;
use Keestash\Core\Service\Core\Environment\EnvironmentService;
use Keestash\Core\Service\Core\Language\LanguageService;
use Keestash\Core\Service\Core\Locale\LocaleService;
use Keestash\Core\Service\Email\EmailService;
use Keestash\Core\Service\Encryption\Credential\CredentialService;
use Keestash\Core\Service\Encryption\Encryption\KeestashEncryptionService;
use Keestash\Core\Service\Encryption\Key\KeyService;
use Keestash\Core\Service\Event\EventDispatcher;
use Keestash\Core\Service\File\FileService;
use Keestash\Core\Service\File\Icon\IconService;
use Keestash\Core\Service\HTTP\PersistenceService;
use Keestash\Core\Service\Organization\OrganizationService;
use Keestash\Core\Service\Router\RouterService;
use Keestash\Core\Service\User\Repository\UserRepositoryService;
use Keestash\Core\Service\User\UserService;
use Keestash\L10N\GetText;
use KSP\App\ILoader;
use KSP\Core\Backend\IBackend;
use KSP\Core\Backend\SQLBackend\ISQLBackend;
use KSP\Core\Cache\ICacheService;
use KSP\Core\Manager\CookieManager\ICookieManager;
use KSP\Core\Manager\EventManager\IEventManager;
use KSP\Core\Manager\FileManager\IFileManager;
use KSP\Core\Manager\LoggerManager\ILoggerManager;
use KSP\Core\Manager\SessionManager\ISessionManager;
use KSP\Core\Manager\SettingManager\ISettingManager;
use KSP\Core\Repository\ApiLog\IApiLogRepository;
use KSP\Core\Repository\AppRepository\IAppRepository;
use KSP\Core\Repository\EncryptionKey\Organization\IOrganizationKeyRepository;
use KSP\Core\Repository\EncryptionKey\User\IUserKeyRepository;
use KSP\Core\Repository\File\IFileRepository;
use KSP\Core\Repository\Job\IJobRepository;
use KSP\Core\Repository\Session\ISessionRepository;
use KSP\Core\Repository\Token\ITokenRepository;
use KSP\Core\Repository\User\IUserRepository;
use KSP\Core\Repository\User\IUserStateRepository;
use KSP\Core\Service\App\IAppService;
use KSP\Core\Service\Config\IConfigService;
use KSP\Core\Service\Controller\IAppRenderer;
use KSP\Core\Service\Core\Environment\IEnvironmentService;
use KSP\Core\Service\Core\Language\ILanguageService;
use KSP\Core\Service\Core\Locale\ILocaleService;
use KSP\Core\Service\Email\IEmailService;
use KSP\Core\Service\Encryption\Credential\ICredentialService;
use KSP\Core\Service\Encryption\IEncryptionService;
use KSP\Core\Service\Encryption\Key\IKeyService;
use KSP\Core\Service\Event\IEventDispatcher;
use KSP\Core\Service\File\Icon\IIconService;
use KSP\Core\Service\File\IFileService;
use KSP\Core\Service\HTTP\IPersistenceService;
use KSP\Core\Service\Organization\IOrganizationService;
use KSP\Core\Service\Router\IRouterService;
use KSP\Core\Service\User\IUserService;
use KSP\Core\Service\User\Repository\IUserRepositoryService;
use KSP\L10N\IL10N;

return [
    IL10N::class                                      => GetText::class,
    IUserService::class                               => UserService::class,
    IApiLogRepository::class                          => ApiLogRepository::class,
    ISQLBackend::class                                => MySQLBackend::class,
    IBackend::class                                   => ISQLBackend::class,
    IConfigService::class                             => ConfigService::class,
    IDateTimeService::class                           => DateTimeService::class,
    IFileRepository::class                            => FileRepository::class,
    IUserRepository::class                            => UserRepository::class,
    ILoggerManager::class                             => LoggerManager::class,
    IUserKeyRepository::class                         => UserKeyRepository::class,
    IKeyService::class                                => KeyService::class,
    IEncryptionService::class                         => KeestashEncryptionService::class,
    IOrganizationKeyRepository::class                 => OrganizationKeyRepository::class,
    IUserStateRepository::class                       => UserStateRepository::class,
    IFileService::class                               => FileService::class,
    ICredentialService::class                         => CredentialService::class,
    IEventManager::class                              => EventManager::class,
    ILoader::class                                    => Loader::class,
    ICacheService::class                              => NullService::class,
    ITokenRepository::class                           => TokenRepository::class,
    IEventDispatcher::class                           => EventDispatcher::class,
    IEmailService::class                              => EmailService::class,
    IAppRepository::class                             => AppRepository::class,
    ICookieManager::class                             => CookieManager::class,
    IOrganizationService::class                       => OrganizationService::class,
    IFileManager::class                               => FileManager::class,
    IJobRepository::class                             => JobRepository::class,
    IPersistenceService::class                        => PersistenceService::class,
    ISessionManager::class                            => SessionManager::class,
    ILocaleService::class                             => LocaleService::class,
    ILanguageService::class                           => LanguageService::class,
    SessionHandlerInterface::class                    => SessionHandler::class,
    ISessionRepository::class                         => SessionRepository::class,
    IEnvironmentService::class                        => EnvironmentService::class,
    IRouterService::class                             => RouterService::class,
    IAppRenderer::class                               => AppRenderer::class,
    ISettingManager::class                            => SettingManager::class,
    IAppService::class                                => AppService::class,
    IUserRepositoryService::class                     => UserRepositoryService::class,
    IIconService::class                               => IconService::class,
    \KSP\Core\Service\File\Upload\IFileService::class => \Keestash\Core\Service\File\Upload\FileService::class
];