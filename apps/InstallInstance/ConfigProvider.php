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

namespace KSA\InstallInstance;

final class ConfigProvider {

    public const INSTALL_INSTANCE                    = '/install_instance[/]';
    public const INSTALL_INSTANCE_CONFIG_DATA        = '/install_instance/config_data[/]';
    public const INSTALL_INSTANCE_END_UPDATE         = '/install_instance/end_update[/]';
    public const INSTALL_INSTANCE_UPDATE_CONFIG      = '/install_instance/update_config[/]';
    public const APP_ID                              = 'install_instance';

    public function __invoke(): array {
        return require __DIR__ . '/config/config.php';
    }

}