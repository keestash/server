<?php
declare(strict_types=1);

/**
 * Keestash
 *
 * Copyright (C) <2023> <Dogan Ucar>
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
use KSA\ForgotPassword\ConfigProvider;
use KSP\Core\DTO\RBAC\IPermission;

return [
    CoreConfigProvider::PERMISSION_MAPPING     => [
        ConfigProvider::RESET_PASSWORD_UPDATE => IPermission::PERMISSION_FORGOT_PASSWORD_RESET_PASSWORD
    ]
    , CoreConfigProvider::PERMISSION_FREE      => [
        ConfigProvider::RESET_PASSWORD_UPDATE
    ]
    , CoreConfigProvider::PERMISSION_LIST      => [
        IPermission::PERMISSION_FORGOT_PASSWORD_RESET_PASSWORD => IPermission::PERMISSION_NAME_FORGOT_PASSWORD_RESET_PASSWORD
    ]
    , CoreConfigProvider::ROLE_PERMISSION_LIST => []
];