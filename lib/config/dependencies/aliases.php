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
use Keestash\Core\Backend\SQLBackend\MySQLBackend;
use Keestash\Core\Repository\ApiLog\ApiLogRepository;
use Keestash\Core\Repository\AppRepository\AppRepository;
use Keestash\Core\Repository\Derivation\DerivationRepository;
use Keestash\Core\Repository\EncryptionKey\Organization\OrganizationKeyRepository;
use Keestash\Core\Repository\EncryptionKey\User\UserKeyRepository;
use Keestash\Core\Repository\File\FileRepository;
use Keestash\Core\Repository\Job\JobRepository;
use Keestash\Core\Repository\LDAP\DefaultConnectionRepository;
use Keestash\Core\Repository\LDAP\DefaultLDAPRepository;
use Keestash\Core\Repository\MailLog\MailLogRepository;
use Keestash\Core\Repository\Payment\DefaultPaymentLogRepository;
use Keestash\Core\Repository\Queue\QueueRepository;
use Keestash\Core\Repository\RBAC\RBACRepository;
use Keestash\Core\Repository\Token\TokenRepository;
use Keestash\Core\Repository\User\UserRepository;
use Keestash\Core\Repository\User\UserStateRepository;
use Keestash\Core\Service\App\AppService;
use Keestash\Core\Service\App\LoaderService;
use Keestash\Core\Service\Cache\NullService;
use Keestash\Core\Service\Config\ConfigService;
use Keestash\Core\Service\Config\IniConfigService;
use Keestash\Core\Service\Core\Access\AccessService;
use Keestash\Core\Service\Core\Environment\EnvironmentService;
use Keestash\Core\Service\Core\Exception\ExceptionHandlerService;
use Keestash\Core\Service\Core\Language\LanguageService;
use Keestash\Core\Service\Core\Locale\LocaleService;
use Keestash\Core\Service\CSV\CSVService;
use Keestash\Core\Service\Derivation\DerivationService;
use Keestash\Core\Service\Email\EmailService;
use Keestash\Core\Service\Encryption\Base64Service;
use Keestash\Core\Service\Encryption\Credential\CredentialService;
use Keestash\Core\Service\Encryption\Encryption\KeestashEncryptionService;
use Keestash\Core\Service\Encryption\Key\KeyService;
use Keestash\Core\Service\Encryption\Mask\StringMaskService;
use Keestash\Core\Service\Encryption\Password\PasswordService;
use Keestash\Core\Service\Event\EventService;
use Keestash\Core\Service\File\FileService;
use Keestash\Core\Service\File\Icon\IconService;
use Keestash\Core\Service\File\Mime\MimeTypeService;
use Keestash\Core\Service\File\RawFile\RawFileService;
use Keestash\Core\Service\HTTP\CORS\ProjectConfiguration;
use Keestash\Core\Service\HTTP\HTTPService;
use Keestash\Core\Service\HTTP\Input\SanitizerService;
use Keestash\Core\Service\HTTP\JWTService;
use Keestash\Core\Service\HTTP\ResponseService;
use Keestash\Core\Service\HTTP\Route\RouteService;
use Keestash\Core\Service\Instance\InstallerService;
use Keestash\Core\Service\L10N\GetText;
use Keestash\Core\Service\LDAP\LDAPService;
use Keestash\Core\Service\Metric\CollectorService;
use Keestash\Core\Service\Organization\OrganizationService;
use Keestash\Core\Service\Payment\DefaultPaymentService;
use Keestash\Core\Service\Permission\PermissionService;
use Keestash\Core\Service\Permission\RoleService;
use Keestash\Core\Service\Phinx\Migrator;
use Keestash\Core\Service\Queue\QueueService;
use Keestash\Core\Service\Router\ApiLogService;
use Keestash\Core\Service\Router\RouterService;
use Keestash\Core\Service\Router\VerificationService;
use Keestash\Core\Service\User\Repository\UserRepositoryService;
use Keestash\Core\Service\User\UserService;
use Keestash\Core\Service\User\UserStateService;
use Keestash\Queue\Handler\EventHandler;
use Keestash\ThirdParty\Mezzio\Cors\UriFactory;
use Keestash\ThirdParty\nikolaposa\RateLimit\FileRateLimiter;
use KSP\Core\Backend\IBackend;
use KSP\Core\Backend\SQLBackend\ISQLBackend;
use KSP\Core\Repository\ApiLog\IApiLogRepository;
use KSP\Core\Repository\AppRepository\IAppRepository;
use KSP\Core\Repository\Derivation\IDerivationRepository;
use KSP\Core\Repository\EncryptionKey\Organization\IOrganizationKeyRepository;
use KSP\Core\Repository\EncryptionKey\User\IUserKeyRepository;
use KSP\Core\Repository\File\IFileRepository;
use KSP\Core\Repository\Job\IJobRepository;
use KSP\Core\Repository\LDAP\IConnectionRepository;
use KSP\Core\Repository\LDAP\ILDAPUserRepository;
use KSP\Core\Repository\MailLog\IMailLogRepository;
use KSP\Core\Repository\Payment\IPaymentLogRepository;
use KSP\Core\Repository\Queue\IQueueRepository;
use KSP\Core\Repository\Token\ITokenRepository;
use KSP\Core\Repository\User\IUserRepository;
use KSP\Core\Repository\User\IUserStateRepository;
use KSP\Core\Service\App\IAppService;
use KSP\Core\Service\App\ILoaderService;
use KSP\Core\Service\Cache\ICacheService;
use KSP\Core\Service\Config\IConfigService;
use KSP\Core\Service\Config\IIniConfigService;
use KSP\Core\Service\Core\Access\IAccessService;
use KSP\Core\Service\Core\Environment\IEnvironmentService;
use KSP\Core\Service\Core\Exception\IExceptionHandlerService;
use KSP\Core\Service\Core\Language\ILanguageService;
use KSP\Core\Service\Core\Locale\ILocaleService;
use KSP\Core\Service\CSV\ICSVService;
use KSP\Core\Service\Derivation\IDerivationService;
use KSP\Core\Service\Email\IEmailService;
use KSP\Core\Service\Encryption\Credential\ICredentialService;
use KSP\Core\Service\Encryption\IBase64Service;
use KSP\Core\Service\Encryption\IEncryptionService;
use KSP\Core\Service\Encryption\IStringMaskService;
use KSP\Core\Service\Encryption\Key\IKeyService;
use KSP\Core\Service\Encryption\Password\IPasswordService;
use KSP\Core\Service\Event\IEventService;
use KSP\Core\Service\File\Icon\IIconService;
use KSP\Core\Service\File\IFileService;
use KSP\Core\Service\File\Mime\IMimeTypeService;
use KSP\Core\Service\File\RawFile\IRawFileService;
use KSP\Core\Service\HTTP\IHTTPService;
use KSP\Core\Service\HTTP\IJWTService;
use KSP\Core\Service\HTTP\Input\ISanitizerService;
use KSP\Core\Service\HTTP\IResponseService;
use KSP\Core\Service\HTTP\Route\IRouteService;
use KSP\Core\Service\Instance\IInstallerService;
use KSP\Core\Service\L10N\IL10N;
use KSP\Core\Service\LDAP\ILDAPService;
use KSP\Core\Service\Metric\ICollectorService;
use KSP\Core\Service\Organization\IOrganizationService;
use KSP\Core\Service\Payment\IPaymentService;
use KSP\Core\Service\Permission\IPermissionService;
use KSP\Core\Service\Permission\IRoleService;
use KSP\Core\Service\Phinx\IMigrator;
use KSP\Core\Service\Queue\IQueueService;
use KSP\Core\Service\Router\ApiLogServiceInterface;
use KSP\Core\Service\Router\IRouterService;
use KSP\Core\Service\Router\IVerificationService;
use KSP\Core\Service\User\IUserService;
use KSP\Core\Service\User\IUserStateService;
use KSP\Core\Service\User\Repository\IUserRepositoryService;
use KSP\Queue\Handler\IEventHandler;
use Mezzio\Cors\Configuration\ConfigurationInterface;
use Prometheus\CollectorRegistry;
use Prometheus\RegistryInterface;
use Psr\Http\Message\UriFactoryInterface;
use RateLimit\RateLimiter;

return [
    // repository
    IApiLogRepository::class                            => ApiLogRepository::class
    , ISQLBackend::class                                => MySQLBackend::class
    , IBackend::class                                   => ISQLBackend::class
    , IFileRepository::class                            => FileRepository::class
    , IUserRepository::class                            => UserRepository::class
    , IUserKeyRepository::class                         => UserKeyRepository::class
    , IOrganizationKeyRepository::class                 => OrganizationKeyRepository::class
    , ITokenRepository::class                           => TokenRepository::class
    , IAppRepository::class                             => AppRepository::class
    , IJobRepository::class                             => JobRepository::class
    , IQueueRepository::class                           => QueueRepository::class
    , IUserStateRepository::class                       => UserStateRepository::class
    , RBACRepositoryInterface::class                    => RBACRepository::class
    , ILDAPUserRepository::class                        => DefaultLDAPRepository::class
    , IConnectionRepository::class                      => DefaultConnectionRepository::class
    , IPaymentLogRepository::class                      => DefaultPaymentLogRepository::class
    , IMailLogRepository::class                         => MailLogRepository::class

    // service
    , IUserService::class                               => UserService::class
    , IUserStateService::class                          => UserStateService::class
    , IConfigService::class                             => ConfigService::class
    , IDateTimeService::class                           => DateTimeService::class
    , IKeyService::class                                => KeyService::class
    , IEncryptionService::class                         => KeestashEncryptionService::class
    , IFileService::class                               => FileService::class
    , ICredentialService::class                         => CredentialService::class
    , ICacheService::class                              => NullService::class
    , IEmailService::class                              => EmailService::class
    , IOrganizationService::class                       => OrganizationService::class
    , ILocaleService::class                             => LocaleService::class
    , ILanguageService::class                           => LanguageService::class
    , IEnvironmentService::class                        => EnvironmentService::class
    , IRouterService::class                             => RouterService::class
    , IAppService::class                                => AppService::class
    , IUserRepositoryService::class                     => UserRepositoryService::class
    , IIconService::class                               => IconService::class
    , \KSP\Core\Service\File\Upload\IFileService::class => \Keestash\Core\Service\File\Upload\FileService::class
    , IStringService::class                             => StringService::class
    , IJWTService::class                                => JWTService::class
    , IHTTPService::class                               => HTTPService::class
    , IInstallerService::class                          => InstallerService::class
    , ApiLogServiceInterface::class                     => ApiLogService::class
    , IAccessService::class                             => AccessService::class
    , \doganoo\DI\HTTP\IHTTPService::class              => \doganoo\DIP\HTTP\HTTPService::class
    , IPasswordService::class                           => PasswordService::class
    , ICSVService::class                                => CSVService::class
    , IMimeTypeService::class                           => MimeTypeService::class
    , IQueueService::class                              => QueueService::class
    , IRawFileService::class                            => RawFileService::class
    , ISanitizerService::class                          => SanitizerService::class
    , IRouteService::class                              => RouteService::class
    , IVerificationService::class                       => VerificationService::class
    , IEventService::class                              => EventService::class
    , ILDAPService::class                               => LDAPService::class
    , IBase64Service::class                             => Base64Service::class
    , IPaymentService::class                            => DefaultPaymentService::class
    , IDerivationService::class                         => DerivationService::class
    , IPermissionService::class                         => PermissionService::class
    , IRoleService::class                               => RoleService::class
    , IStringMaskService::class                         => StringMaskService::class
    , IResponseService::class                           => ResponseService::class
    , IExceptionHandlerService::class                   => ExceptionHandlerService::class
    , IIniConfigService::class                          => IniConfigService::class
    , ICollectorService::class                          => CollectorService::class
    , RegistryInterface::class                          => CollectorRegistry::class

    , IL10N::class                                      => GetText::class
    , ILoaderService::class                             => LoaderService::class
    , SessionHandlerInterface::class                    => SessionHandler::class
    , IMigrator::class                                  => Migrator::class
    , ConfigurationInterface::class                     => ProjectConfiguration::class

    // handler
    , IEventHandler::class                              => EventHandler::class

    // third party
    , ClientInterface::class                            => Client::class
    , RateLimiter::class                                => FileRateLimiter::class
    , UriFactoryInterface::class                        => UriFactory::class
];
