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
use doganoo\DI\Object\String\IStringService;
use doganoo\DIP\DateTime\DateTimeService;
use doganoo\DIP\Object\String\StringService;
use doganoo\SimpleRBAC\Repository\RBACRepositoryInterface;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use Keestash\App\Cors\ProjectConfiguration;
use Keestash\App\Loader\Loader;
use Keestash\Core\Backend\MySQLBackend;
use Keestash\Core\Cache\NullService;
use Keestash\Core\Manager\EventManager\EventManager;
use Keestash\Core\Manager\FileManager\FileManager;
use Keestash\Core\Manager\LoggerManager\LoggerManager;
use Keestash\Core\Manager\SettingManager\SettingManager;
use Keestash\Core\Repository\ApiLog\ApiLogRepository;
use Keestash\Core\Repository\AppRepository\AppRepository;
use Keestash\Core\Repository\EncryptionKey\Organization\OrganizationKeyRepository;
use Keestash\Core\Repository\EncryptionKey\User\UserKeyRepository;
use Keestash\Core\Repository\File\FileRepository;
use Keestash\Core\Repository\Job\JobRepository;
use Keestash\Core\Repository\Queue\QueueRepository;
use Keestash\Core\Repository\RBAC\RBACRepository;
use Keestash\Core\Repository\Session\SessionRepository;
use Keestash\Core\Repository\Token\TokenRepository;
use Keestash\Core\Repository\User\UserRepository;
use Keestash\Core\Repository\User\UserStateRepository;
use Keestash\Core\Service\App\AppService;
use Keestash\Core\Service\Config\ConfigService;
use Keestash\Core\Service\Controller\AppRenderer;
use Keestash\Core\Service\Core\Access\IAccessService;
use Keestash\Core\Service\Core\Environment\EnvironmentService;
use Keestash\Core\Service\Core\Language\LanguageService;
use Keestash\Core\Service\Core\Locale\LocaleService;
use Keestash\Core\Service\CSV\CSVService;
use Keestash\Core\Service\Email\EmailService;
use Keestash\Core\Service\Encryption\Credential\CredentialService;
use Keestash\Core\Service\Encryption\Encryption\KeestashEncryptionService;
use Keestash\Core\Service\Encryption\Key\KeyService;
use Keestash\Core\Service\Encryption\Password\PasswordService;
use Keestash\Core\Service\File\FileService;
use Keestash\Core\Service\File\Icon\IconService;
use Keestash\Core\Service\File\Mime\MimeTypeService;
use Keestash\Core\Service\HTTP\HTTPService;
use Keestash\Core\Service\HTTP\JWTService;
use Keestash\Core\Service\Instance\InstallerService;
use Keestash\Core\Service\Organization\OrganizationService;
use Keestash\Core\Service\Phinx\Migrator;
use Keestash\Core\Service\Queue\QueueService;
use Keestash\Core\Service\Router\ApiRequestService;
use Keestash\Core\Service\Router\RouterService;
use Keestash\Core\Service\User\Repository\UserRepositoryService;
use Keestash\Core\Service\User\UserService;
use Keestash\Core\System\RateLimit\FileRateLimiter;
use Keestash\L10N\GetText;
use Keestash\Queue\Handler\EventHandler;
use KSP\App\ILoader;
use KSP\Core\Backend\IBackend;
use KSP\Core\Backend\SQLBackend\ISQLBackend;
use KSP\Core\Cache\ICacheService;
use KSP\Core\Manager\EventManager\IEventManager;
use KSP\Core\Manager\FileManager\IFileManager;
use KSP\Core\Manager\LoggerManager\ILoggerManager;
use KSP\Core\Manager\SettingManager\ISettingManager;
use KSP\Core\Repository\ApiLog\IApiLogRepository;
use KSP\Core\Repository\AppRepository\IAppRepository;
use KSP\Core\Repository\EncryptionKey\Organization\IOrganizationKeyRepository;
use KSP\Core\Repository\EncryptionKey\User\IUserKeyRepository;
use KSP\Core\Repository\File\IFileRepository;
use KSP\Core\Repository\Job\IJobRepository;
use KSP\Core\Repository\Queue\IQueueRepository;
use KSP\Core\Repository\Session\ISessionRepository;
use KSP\Core\Repository\Token\ITokenRepository;
use KSP\Core\Repository\User\IUserRepository;
use KSP\Core\Repository\User\IUserStateRepository;
use KSP\Core\Service\App\IAppService;
use KSP\Core\Service\Config\IConfigService;
use KSP\Core\Service\Controller\IAppRenderer;
use KSP\Core\Service\Core\Access\AccessService;
use KSP\Core\Service\Core\Environment\IEnvironmentService;
use KSP\Core\Service\Core\Language\ILanguageService;
use KSP\Core\Service\Core\Locale\ILocaleService;
use KSP\Core\Service\CSV\ICSVService;
use KSP\Core\Service\Email\IEmailService;
use KSP\Core\Service\Encryption\Credential\ICredentialService;
use KSP\Core\Service\Encryption\IEncryptionService;
use KSP\Core\Service\Encryption\Key\IKeyService;
use KSP\Core\Service\Encryption\Password\IPasswordService;
use KSP\Core\Service\File\Icon\IIconService;
use KSP\Core\Service\File\IFileService;
use KSP\Core\Service\File\Mime\IMimeTypeService;
use KSP\Core\Service\HTTP\IHTTPService;
use KSP\Core\Service\HTTP\IJWTService;
use KSP\Core\Service\Instance\IInstallerService;
use KSP\Core\Service\Organization\IOrganizationService;
use KSP\Core\Service\Phinx\IMigrator;
use KSP\Core\Service\Queue\IQueueService;
use KSP\Core\Service\Router\IApiRequestService;
use KSP\Core\Service\Router\IRouterService;
use KSP\Core\Service\User\IUserService;
use KSP\Core\Service\User\Repository\IUserRepositoryService;
use KSP\L10N\IL10N;
use KSP\Queue\Handler\IEventHandler;
use Mezzio\Cors\Configuration\ConfigurationInterface;
use RateLimit\RateLimiter;

return [
    // repository
    IApiLogRepository::class                          => ApiLogRepository::class,
    ISQLBackend::class                                => MySQLBackend::class,
    IBackend::class                                   => ISQLBackend::class,
    IFileRepository::class                            => FileRepository::class,
    IUserRepository::class                            => UserRepository::class,
    IUserKeyRepository::class                         => UserKeyRepository::class,
    IOrganizationKeyRepository::class                 => OrganizationKeyRepository::class,
    ITokenRepository::class                           => TokenRepository::class,
    IAppRepository::class                             => AppRepository::class,
    IJobRepository::class                             => JobRepository::class,
    ISessionRepository::class                         => SessionRepository::class,
    IQueueRepository::class                           => QueueRepository::class,
    IUserStateRepository::class                       => UserStateRepository::class,
    RBACRepositoryInterface::class                    => RBACRepository::class,

    // service
    IUserService::class                               => UserService::class,
    IConfigService::class                             => ConfigService::class,
    IDateTimeService::class                           => DateTimeService::class,
    IKeyService::class                                => KeyService::class,
    IEncryptionService::class                         => KeestashEncryptionService::class,
    IFileService::class                               => FileService::class,
    ICredentialService::class                         => CredentialService::class,
    ICacheService::class                              => NullService::class,
    IEmailService::class                              => EmailService::class,
    IOrganizationService::class                       => OrganizationService::class,
    ILocaleService::class                             => LocaleService::class,
    ILanguageService::class                           => LanguageService::class,
    IEnvironmentService::class                        => EnvironmentService::class,
    IRouterService::class                             => RouterService::class,
    IAppService::class                                => AppService::class,
    IUserRepositoryService::class                     => UserRepositoryService::class,
    IIconService::class                               => IconService::class,
    \KSP\Core\Service\File\Upload\IFileService::class => \Keestash\Core\Service\File\Upload\FileService::class,
    IStringService::class                             => StringService::class,
    IJWTService::class                                => JWTService::class,
    IHTTPService::class                               => HTTPService::class,
    IInstallerService::class                          => InstallerService::class,
    IApiRequestService::class                         => ApiRequestService::class,
    IAccessService::class                             => AccessService::class,
    \doganoo\DI\HTTP\IHTTPService::class              => \doganoo\DIP\HTTP\HTTPService::class,
    IPasswordService::class                           => PasswordService::class,
    ICSVService::class                                => CSVService::class,
    IMimeTypeService::class                           => MimeTypeService::class,
    IQueueService::class                              => QueueService::class,

    // manager
    ILoggerManager::class                             => LoggerManager::class,
    IEventManager::class                              => EventManager::class,
    IFileManager::class                               => FileManager::class,
    ISettingManager::class                            => SettingManager::class,

    IL10N::class                   => GetText::class,
    ILoader::class                 => Loader::class,
    SessionHandlerInterface::class => SessionHandler::class,
    IAppRenderer::class            => AppRenderer::class,
    IMigrator::class               => Migrator::class,
    ConfigurationInterface::class  => ProjectConfiguration::class,

    // handler
    IEventHandler::class           => EventHandler::class,

    // third party
    ClientInterface::class         => Client::class,

    // system
    RateLimiter::class             => FileRateLimiter::class,
];