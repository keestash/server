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

use doganoo\SimpleRBAC\ConfigProvider as SimpleRBACConfigProvider;
use Keestash\ConfigProvider as CoreConfigProvider;
use KSA\Activity\ConfigProvider as ActivityConfigProvider;
use KSA\LDAP\ConfigProvider as LDAPConfigProvider;
use KSA\Login\ConfigProvider as LoginConfigProvider;
use KSA\PasswordManager\ConfigProvider as PasswordManagerConfigProvider;
use KSA\Payment\ConfigProvider as PaymentConfigProvider;
use KSA\Marketing\ConfigProvider as MarketingConfigProvider;
use KSA\Metric\ConfigProvider as MetricConfigProvider;
use KSA\Register\ConfigProvider as RegisterConfigProvider;
use KSA\Settings\ConfigProvider as SettingsConfigProvider;
use KSA\Instance\ConfigProvider as InstanceConfigProvider;
use Laminas\Diactoros\ConfigProvider as DiactorosConfigProvider;
use Laminas\HttpHandlerRunner\ConfigProvider as HttpHandlerRunnerConfigProvider;
use Laminas\Router\ConfigProvider as LaminasRouterConfigProvider;
use Laminas\Validator\ConfigProvider as ValidatorConfigProvider;
use Mezzio\ConfigProvider as MezzioConfigProvider;
use Mezzio\Cors\ConfigProvider as CorsConfigProvider;
use Mezzio\Helper\ConfigProvider as HelperConfigProvider;
use Mezzio\Router\ConfigProvider as RouterConfigProvider;
use Mezzio\Router\LaminasRouter\ConfigProvider as MezzioRouterConfigProvider;
use Mezzio\Twig\ConfigProvider as TwigConfigProvider;

return [
    // framework
    TwigConfigProvider::class
    , CorsConfigProvider::class
    , HttpHandlerRunnerConfigProvider::class
    , LaminasRouterConfigProvider::class
    , MezzioRouterConfigProvider::class
    , ValidatorConfigProvider::class
    , DiactorosConfigProvider::class
    , RouterConfigProvider::class
    , HelperConfigProvider::class
    , MezzioConfigProvider::class

    // Keestash
    , CoreConfigProvider::class
    , LoginConfigProvider::class
    , class_exists(LDAPConfigProvider::class)
        ? LDAPConfigProvider::class
        : function (): array {
            return [];
        }
    , PasswordManagerConfigProvider::class
    , RegisterConfigProvider::class
    , SettingsConfigProvider::class
    , class_exists(PaymentConfigProvider::class)
        ? PaymentConfigProvider::class
        : function (): array {
            return [];
        }
    , class_exists(MarketingConfigProvider::class)
        ? MarketingConfigProvider::class
        : function (): array {
            return [];
        }
    , class_exists(MetricConfigProvider::class)
        ? MetricConfigProvider::class
        : function (): array {
            return [];
        }
    , ActivityConfigProvider::class
    , InstanceConfigProvider::class

    // Third Party libs
    , SimpleRBACConfigProvider::class
];
