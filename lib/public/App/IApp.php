<?php
declare(strict_types=1);
/**
 * Keestash
 *
 * Copyright (C) <2019> <Dogan Ucar>
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

namespace KSP\App;

use KSP\Core\DTO\Entity\IJsonObject;

interface IApp extends IJsonObject {

    public const CONFIG_PROVIDER_API_ROUTER             = 'router.api.provider.config';
    public const CONFIG_PROVIDER_WEB_ROUTER             = 'router.web.provider.config';
    public const CONFIG_PROVIDER_WEB_ROUTER_STYLESHEETS = 'stylesheets.router.web.provider.config';
    public const CONFIG_PROVIDER_WEB_ROUTER_SCRIPTS     = 'scripts.router.web.provider.config';
    public const CONFIG_PROVIDER_EVENTS                 = 'events.provider.config';
    public const CONFIG_PROVIDER_COMMANDS               = 'commands.provider.config';
    public const CONFIG_PROVIDER_ROUTES                 = 'routes.provider.config';
    public const CONFIG_PROVIDER_PUBLIC_ROUTES          = 'routes.public.provider.config';
    public const CONFIG_PROVIDER_SETTINGS               = 'settings.provider.config';

    public const FIELD_ID                       = "id";
    public const FIELD_ORDER                    = "order";
    public const FIELD_NAME                     = "name";
    public const FIELD_DISABLE                  = "disable";
    public const FIELD_BASE_ROUTE               = "base_route";
    public const FIELD_FA_ICON_CLASS            = "fa-icon-class";
    public const FIELD_VERSION                  = "version";
    public const FIELD_VERSION_STRING           = "version_string";
    public const FIELD_TYPE                     = "type";
    public const FIELD_SHOW_ICON                = "show-icon";
    public const FIELD_BACKGROUND_JOBS          = "background_jobs";
    public const FIELD_DEMONSTRATE              = "demonstrate";
    public const FIELD_BACKGROUND_JOBS_TYPE     = "type";
    public const FIELD_BACKGROUND_JOBS_INTERVAL = "interval";
    public const FIELD_SETTINGS                 = "settings";
    public const FIELD_STYLESHEETS              = "stylesheets";

    public function getId(): string;

    public function getOrder(): int;

    public function getName(): string;

    public function getAppPath(): string;

    public function getTemplatePath(): string;

    public function getStringPath(): string;

    public function getFAIconClass(): string;

    public function getBaseRoute(): string;

    public function getVersion(): int;

    public function getVersionString(): string;

    public function getType(): string;

    public function showIcon(): bool;

    public function getBackgroundJobs(): array;

    public function getSettings(): array;

    public function getStyleSheets(): array;

    public function isDemonstrateable(): bool;

}
