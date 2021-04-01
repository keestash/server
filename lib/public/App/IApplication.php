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

use doganoo\PHPAlgorithms\Datastructure\Table\HashTable;

interface IApplication {

    public const CONFIG_PROVIDER_APPLICATION     = 'keestash.application.config';
    public const CONFIG_PROVIDER_ROUTES          = 'keestash.application.routes';
    public const CONFIG_PROVIDER_API_ROUTES      = 'keestash.application.routes.api';
    public const CONFIG_PROVIDER_PUBLIC_ROUTES   = 'keestash.application.routes.public';
    public const CONFIG_PROVIDER_STYLESHEETS     = 'keestash.application.stylesheets';
    public const CONFIG_PROVIDER_JAVASCRIPT      = 'keestash.application.javascript';
    public const CONFIG_PROVIDER_SETTINGS        = 'keestash.application.settings';
    public const CONFIG_PROVIDER_TEMPLATES       = 'keestash.application.templates';
    public const CONFIG_PROVIDER_CONTEXT_SETTING = 'keestash.application.context.setting';
    public const CONFIG_PROVIDER_SERVICES        = 'keestash.application.services';
    public const CONFIG_PROVIDER_EVENTS          = 'keestash.application.events';

    public function __construct(IApp $app);

    public function register(): void;

    public function getFrontendTemplates(): HashTable;

}