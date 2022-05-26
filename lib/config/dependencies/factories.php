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

use Doctrine\DBAL\Connection;
use doganoo\DIP\DateTime\DateTimeService;
use doganoo\DIP\Object\String\StringService;
use Keestash\App\Config\Diff;
use Keestash\App\Cors\ProjectConfiguration;
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
use Keestash\Core\Repository\Instance\InstanceDB;
use Keestash\Core\Repository\Instance\InstanceRepository;
use Keestash\Core\Repository\Job\JobRepository;
use Keestash\Core\Repository\Queue\QueueRepository;
use Keestash\Core\Repository\Session\SessionRepository;
use Keestash\Core\Repository\Token\TokenRepository;
use Keestash\Core\Repository\User\UserRepository;
use Keestash\Core\Repository\User\UserStateRepository;
use Keestash\Core\Service\App\AppService;
use Keestash\Core\Service\App\InstallerService;
use Keestash\Core\Service\Config\ConfigService;
use Keestash\Core\Service\Config\IniConfigService;
use Keestash\Core\Service\Controller\AppRenderer;
use Keestash\Core\Service\Core\Environment\EnvironmentService;
use Keestash\Core\Service\Core\Language\LanguageService;
use Keestash\Core\Service\Core\Locale\LocaleService;
use Keestash\Core\Service\Email\EmailService;
use Keestash\Core\Service\Encryption\Credential\CredentialService;
use Keestash\Core\Service\Encryption\Encryption\KeestashEncryptionService;
use Keestash\Core\Service\Encryption\Key\KeyService;
use Keestash\Core\Service\Encryption\Password\PasswordService;
use Keestash\Core\Service\Event\EventDispatcher;
use Keestash\Core\Service\File\FileService;
use Keestash\Core\Service\File\Icon\IconService;
use Keestash\Core\Service\File\RawFile\RawFileService;
use Keestash\Core\Service\HTTP\HTTPService;
use Keestash\Core\Service\HTTP\Input\SanitizerService;
use Keestash\Core\Service\HTTP\JWTService;
use Keestash\Core\Service\HTTP\PersistenceService;
use Keestash\Core\Service\Organization\OrganizationService;
use Keestash\Core\Service\Phinx\Migrator;
use Keestash\Core\Service\Queue\MessageService;
use Keestash\Core\Service\ReflectionService;
use Keestash\Core\Service\Router\ApiRequestService;
use Keestash\Core\Service\Router\RouterService;
use Keestash\Core\Service\Router\Verification;
use Keestash\Core\Service\Stylesheet\Compiler;
use Keestash\Core\Service\User\Repository\UserRepositoryService;
use Keestash\Core\Service\User\UserService;
use Keestash\Core\System\Installation\App\LockHandler;
use Keestash\Factory\App\Config\DiffFactory;
use Keestash\Factory\App\Cors\ProjectConfigurationFactory;
use Keestash\Factory\App\Loader\LoaderFactory;
use Keestash\Factory\Core\Backend\MySQLBackendFactory;
use Keestash\Factory\Core\Builder\Validator\EmailValidatorFactory;
use Keestash\Factory\Core\Builder\Validator\PhoneValidatorFactory;
use Keestash\Factory\Core\Builder\Validator\UriValidatorFactory;
use Keestash\Factory\Core\Legacy\LegacyFactory;
use Keestash\Factory\Core\Logger\LoggerFactory;
use Keestash\Factory\Core\Manager\CookieManager\CookieManagerFactory;
use Keestash\Factory\Core\Manager\EventManager\EventManagerFactory;
use Keestash\Factory\Core\Manager\FileManager\FileManagerFactory;
use Keestash\Factory\Core\Manager\Logger\LoggerManagerFactory;
use Keestash\Factory\Core\Manager\SessionManager\SessionHandlerFactory;
use Keestash\Factory\Core\Manager\SessionManager\SessionManagerFactory;
use Keestash\Factory\Core\Repository\ApiLogRepositoryFactory;
use Keestash\Factory\Core\Repository\AppRepository\AppRepositoryFactory;
use Keestash\Factory\Core\Repository\EncryptionKey\Organization\OrganizationKeyRepositoryFactory;
use Keestash\Factory\Core\Repository\EncryptionKey\User\UserKeyRepositoryFactory;
use Keestash\Factory\Core\Repository\FileRepositoryFactory;
use Keestash\Factory\Core\Repository\Instance\InstanceDBFactory;
use Keestash\Factory\Core\Repository\Instance\InstanceRepositoryFactory;
use Keestash\Factory\Core\Repository\Job\JobRepositoryFactory;
use Keestash\Factory\Core\Repository\Queue\QueueRepositoryFactory;
use Keestash\Factory\Core\Repository\Session\SessionRepositoryFactory;
use Keestash\Factory\Core\Repository\Token\TokenRepositoryFactory;
use Keestash\Factory\Core\Repository\User\UserStateRepositoryFactory;
use Keestash\Factory\Core\Repository\UserRepositoryFactory;
use Keestash\Factory\Core\Service\App\InstallerServiceFactory;
use Keestash\Factory\Core\Service\Config\ConfigServiceFactory;
use Keestash\Factory\Core\Service\Controller\AppRendererFactory;
use Keestash\Factory\Core\Service\Core\Language\LanguageServiceFactory;
use Keestash\Factory\Core\Service\Email\EmailServiceFactory;
use Keestash\Factory\Core\Service\Encryption\Credential\CredentialServiceFactory;
use Keestash\Factory\Core\Service\Encryption\KeestashEncryptionServiceFactory;
use Keestash\Factory\Core\Service\Encryption\Key\KeyServiceFactory;
use Keestash\Factory\Core\Service\Event\EventDispatcherFactory;
use Keestash\Factory\Core\Service\File\FileServiceFactory;
use Keestash\Factory\Core\Service\File\RawFile\RawFileServiceFactory;
use Keestash\Factory\Core\Service\HTTP\HTTPServiceFactory;
use Keestash\Factory\Core\Service\HTTP\JWTServiceFactory;
use Keestash\Factory\Core\Service\HTTP\PersistenceServiceFactory;
use Keestash\Factory\Core\Service\HTTP\SanitizerServiceFactory;
use Keestash\Factory\Core\Service\Organization\OrganizationServiceFactory;
use Keestash\Factory\Core\Service\Phinx\MigratorFactory;
use Keestash\Factory\Core\Service\Router\ApiRequestServiceFactory;
use Keestash\Factory\Core\Service\Router\RouterServiceFactory;
use Keestash\Factory\Core\Service\Router\VerificationFactory;
use Keestash\Factory\Core\Service\Stylesheet\CompilerFactory;
use Keestash\Factory\Core\Service\User\Repository\UserRepositoryServiceFactory;
use Keestash\Factory\Core\Service\User\UserServiceFactory;
use Keestash\Factory\Core\System\Installation\App\AppLockHandlerFactory;
use Keestash\Factory\Core\System\Installation\Instance\InstanceLockHandlerFactory;
use Keestash\Factory\Middleware\Api\ExceptionHandlerMiddlewareFactory as ApiExceptionHandlerMiddlewareFactory;
use Keestash\Factory\Middleware\ApplicationStartedMiddlewareFactory;
use Keestash\Factory\Middleware\Web\ExceptionHandlerMiddlewareFactory as WebExceptionHandlerMiddlewareFactory;
use Keestash\Factory\Queue\Handler\EmailHandlerFactory;
use Keestash\Factory\Queue\WorkerFactory;
use Keestash\Middleware\Api\ExceptionHandlerMiddleware as ApiExceptionHandlerMiddlerware;
use Keestash\Middleware\ApplicationStartedMiddleware;
use Keestash\Factory\Middleware\AppsInstalledMiddlewareFactory;
use Keestash\Factory\Middleware\DispatchMiddlewareFactory;
use Keestash\Factory\Middleware\InstanceInstalledMiddlewareFactory;
use Keestash\Factory\Middleware\Api\KeestashHeaderMiddlewareFactory;
use Keestash\Factory\Middleware\Web\LoggedInMiddlewareFactory;
use Keestash\Factory\Middleware\Web\SessionHandlerMiddlewareFactory;
use Keestash\Factory\Middleware\Web\UserActiveMiddlewareFactory;
use Keestash\Factory\ThirdParty\Doctrine\ConnectionFactory;
use Keestash\Factory\ThirdParty\doganoo\DateTimeServiceFactory;
use Keestash\L10N\GetText;
use Keestash\Legacy\Legacy;
use Keestash\Middleware\AppsInstalledMiddleware;
use Keestash\Middleware\DispatchMiddleware;
use Keestash\Middleware\InstanceInstalledMiddleware;
use Keestash\Middleware\Api\KeestashHeaderMiddleware;
use Keestash\Middleware\Web\ExceptionHandlerMiddleware as WebExceptionHandlerMiddleware;
use Keestash\Middleware\Web\LoggedInMiddleware;
use Keestash\Middleware\Web\SessionHandlerMiddleware;
use Keestash\Middleware\Api\UserActiveMiddleware;
use Keestash\Queue\Handler\EmailHandler;
use KSA\PasswordManager\Service\Node\Edge\EdgeService;
use KSP\Core\ILogger\ILogger;
use KSP\Core\Service\Core\Access\AccessService;
use Laminas\I18n\Validator\PhoneNumber as PhoneValidator;
use Laminas\ServiceManager\Factory\InvokableFactory;
use Laminas\Validator\EmailAddress as EmailValidator;
use Laminas\Validator\Uri as UriValidator;

return [
    // App
    ProjectConfiguration::class      => ProjectConfigurationFactory::class,

    // repository
    ApiLogRepository::class          => ApiLogRepositoryFactory::class,
    MySQLBackend::class              => MySQLBackendFactory::class,
    FileRepository::class            => FileRepositoryFactory::class,
    UserRepository::class            => UserRepositoryFactory::class,
    UserKeyRepository::class         => UserKeyRepositoryFactory::class,
    OrganizationKeyRepository::class => OrganizationKeyRepositoryFactory::class,
    UserStateRepository::class       => UserStateRepositoryFactory::class,
    InstanceRepository::class        => InstanceRepositoryFactory::class,
    TokenRepository::class           => TokenRepositoryFactory::class,
    AppRepository::class             => AppRepositoryFactory::class,
    SessionRepository::class         => SessionRepositoryFactory::class,
    QueueRepository::class           => QueueRepositoryFactory::class,

    LoggerManager::class                                           => LoggerManagerFactory::class,
    ILogger::class                                                 => LoggerFactory::class,
    Legacy::class                                                  => LegacyFactory::class,
    EventManager::class                                            => EventManagerFactory::class,
    Loader::class                                                  => LoaderFactory::class,
    Verification::class                                            => VerificationFactory::class,
    EventDispatcher::class                                         => EventDispatcherFactory::class,
    InstanceDB::class                                              => InstanceDBFactory::class,
    CookieManager::class                                           => CookieManagerFactory::class,
    FileManager::class                                             => FileManagerFactory::class,
    Migrator::class                                                => MigratorFactory::class,
    JobRepository::class                                           => JobRepositoryFactory::class,
    LockHandler::class                                             => AppLockHandlerFactory::class,
    \Keestash\Core\System\Installation\Instance\LockHandler::class => InstanceLockHandlerFactory::class,
    SessionManager::class                                          => SessionManagerFactory::class,
    SessionHandler::class                                          => SessionHandlerFactory::class,
    LoggedInMiddleware::class                                      => LoggedInMiddlewareFactory::class,
    AppRenderer::class                                             => AppRendererFactory::class,
    Diff::class                                                    => DiffFactory::class,
    Compiler::class                                                => CompilerFactory::class,
    JWTService::class                                              => JWTServiceFactory::class,

    // builder
    EmailValidator::class                                          => EmailValidatorFactory::class,
    PhoneValidator::class                                          => PhoneValidatorFactory::class,
    UriValidator::class                                            => UriValidatorFactory::class,

    // middleware
    InstanceInstalledMiddleware::class                             => InstanceInstalledMiddlewareFactory::class,
    AppsInstalledMiddleware::class                                 => AppsInstalledMiddlewareFactory::class,
    DispatchMiddleware::class                                      => DispatchMiddlewareFactory::class,
    ApplicationStartedMiddleware::class                            => ApplicationStartedMiddlewareFactory::class,
    // api
    KeestashHeaderMiddleware::class                                => KeestashHeaderMiddlewareFactory::class,
    ApiExceptionHandlerMiddlerware::class                          => ApiExceptionHandlerMiddlewareFactory::class,

    // web
    SessionHandlerMiddleware::class                                => SessionHandlerMiddlewareFactory::class,
    UserActiveMiddleware::class                                    => UserActiveMiddlewareFactory::class,
    WebExceptionHandlerMiddleware::class                           => WebExceptionHandlerMiddlewareFactory::class,

    // ThirdParty
    DateTimeService::class                                         => DateTimeServiceFactory::class,
    Connection::class                                              => ConnectionFactory::class,
    \doganoo\DIP\HTTP\HTTPService::class                           => InvokableFactory::class,

    // service
    UserService::class                                             => UserServiceFactory::class,
    ConfigService::class                                           => ConfigServiceFactory::class,
    KeyService::class                                              => KeyServiceFactory::class,
    KeestashEncryptionService::class                               => KeestashEncryptionServiceFactory::class,
    FileService::class                                             => FileServiceFactory::class,
    RawFileService::class                                          => RawFileServiceFactory::class,
    CredentialService::class                                       => CredentialServiceFactory::class,
    EmailService::class                                            => EmailServiceFactory::class,
    OrganizationService::class                                     => OrganizationServiceFactory::class,
    InstallerService::class                                        => InstallerServiceFactory::class,
    HTTPService::class                                             => HTTPServiceFactory::class,
    \Keestash\Core\Service\Instance\InstallerService::class        => \Keestash\Factory\Core\Service\Instance\InstallerServiceFactory::class,
    PersistenceService::class                                      => PersistenceServiceFactory::class,
    LanguageService::class                                         => LanguageServiceFactory::class,
    RouterService::class                                           => RouterServiceFactory::class,
    UserRepositoryService::class                                   => UserRepositoryServiceFactory::class,
    \Keestash\Core\Service\File\Upload\FileService::class          => \Keestash\Factory\Core\Service\Upload\FileServiceFactory::class,
    SanitizerService::class                                        => SanitizerServiceFactory::class,
    ApiRequestService::class                                       => ApiRequestServiceFactory::class,
    NullService::class                                             => InvokableFactory::class,
    ReflectionService::class                                       => InvokableFactory::class,
    LocaleService::class                                           => InvokableFactory::class,
    EnvironmentService::class                                      => InvokableFactory::class,
    AppService::class                                              => InvokableFactory::class,
    EdgeService::class                                             => InvokableFactory::class,
    IconService::class                                             => InvokableFactory::class,
    PasswordService::class                                         => InvokableFactory::class,
    IniConfigService::class                                        => InvokableFactory::class,
    StringService::class                                           => InvokableFactory::class,
    AccessService::class                                           => InvokableFactory::class,
    MessageService::class                                          => InvokableFactory::class,

    GetText::class                                            => InvokableFactory::class,
    \Symfony\Component\EventDispatcher\EventDispatcher::class => InvokableFactory::class,
    \doganoo\PHPUtil\HTTP\Session::class                      => InvokableFactory::class,
    SettingManager::class                                     => InvokableFactory::class,
    HTMLPurifier::class                                       => InvokableFactory::class,

    // command
    \Keestash\Queue\Worker::class                             => WorkerFactory::class,

    // handler
    EmailHandler::class                                       => EmailHandlerFactory::class
];