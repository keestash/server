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

namespace KSP\Api;

interface IRequest {

    public const ATTRIBUTE_NAME_APPLICATION_START          = 'start.application.name.attribute';
    public const ATTRIBUTE_NAME_ROUTES_TO_INSTANCE_INSTALL = 'install.instance.to.routes.name.attribute';
    public const ATTRIBUTE_NAME_ROUTES_TO_INSTALL          = 'install.to.routes.name.attribute';
    public const ATTRIBUTE_NAME_IS_PUBLIC                  = 'public.is.name.attribute';
    public const ATTRIBUTE_NAME_ENVIRONMENT                = 'environment.name.attribute';
    public const ATTRIBUTE_NAME_INSTANCE_ID_AND_HASH_GIVEN = 'given.hash.and.id.instance.name.attribute';

}