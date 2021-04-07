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

use Keestash\ConfigProvider as CoreConfigProvider;
use KSA\About\ConfigProvider as AboutConfigProvider;
use KSA\Apps\ConfigProvider as AppsConfigProvider;
use KSA\ForgotPassword\ConfigProvider as ForgotPasswordConfigProvider;
use KSA\GeneralApi\ConfigProvider as GeneralApiConfigProvider;
use KSA\Install\ConfigProvider as InstallConfigProvider;
use KSA\InstallInstance\ConfigProvider as InstallInstanceConfigProvider;
use KSA\Login\ConfigProvider as LoginConfigProvider;
use KSA\PasswordManager\ConfigProvider as PasswordManagerConfigProvider;
use KSA\Register\ConfigProvider as RegisterConfigProvider;
use KSA\Settings\ConfigProvider as SettingsConfigProvider;
use KSA\TNC\ConfigProvider as TNCConfigProvider;
use KSA\Users\ConfigProvider as UsersConfigProvider;
use Laminas\Config\Config;
use Laminas\ConfigAggregator\ConfigAggregator;
use Laminas\Diactoros\ConfigProvider as DiactorosConfigProvider;
use Laminas\HttpHandlerRunner\ConfigProvider as HttpHandlerRunnerConfigProvider;
use Laminas\Router\ConfigProvider as LaminasRouterConfigProvider;
use Laminas\ServiceManager\ServiceManager;
use Laminas\Validator\ConfigProvider as ValidatorConfigProvider;
use Mezzio\ConfigProvider as MezzioConfigProvider;
use Mezzio\Helper\ConfigProvider as HelperConfigProvider;
use Mezzio\Router\ConfigProvider as RouterConfigProvider;
use Mezzio\Router\LaminasRouter\ConfigProvider as MezzioRouterConfigProvider;
use Mezzio\Twig\ConfigProvider as TwigConfigProvider;

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../lib/versioncheck.php';
require_once __DIR__ . '/../lib/filecheck.php';
require_once __DIR__ . '/../lib/extensioncheck.php';

$configs = [
    // framework
    TwigConfigProvider::class,
    HttpHandlerRunnerConfigProvider::class,
    LaminasRouterConfigProvider::class,
    MezzioRouterConfigProvider::class,
    ValidatorConfigProvider::class,
    DiactorosConfigProvider::class,
    RouterConfigProvider::class,
    HelperConfigProvider::class,
    MezzioConfigProvider::class,

    // Keestash
    CoreConfigProvider::class,
    AboutConfigProvider::class,
    AppsConfigProvider::class,
    ForgotPasswordConfigProvider::class,
    GeneralApiConfigProvider::class,
    InstallConfigProvider::class,
    InstallInstanceConfigProvider::class,
    LoginConfigProvider::class,
    PasswordManagerConfigProvider::class,
    RegisterConfigProvider::class,
    SettingsConfigProvider::class,
    TNCConfigProvider::class,
    UsersConfigProvider::class,
];

$configAggregator = new ConfigAggregator(
    $configs
    , __DIR__ . '/../config/cache/config-cache.php'
);

$config       = $configAggregator->getMergedConfig();
$configObject = new Config($config);

$dependencies                       = $config['dependencies'];
$dependencies['services']['config'] = $config;

$serviceManager = new ServiceManager(
    $dependencies
);

unset($config['dependencies']);

$serviceManager->setFactory(
    Config::class, fn() => $configObject
);

return $serviceManager;