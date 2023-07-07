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

namespace KSA\Settings\Repository;

use doganoo\PHPAlgorithms\Datastructure\Lists\ArrayList\ArrayList;
use KSA\Settings\Entity\UserSetting;
use KSA\Settings\Exception\SettingNotDeletedException;
use KSA\Settings\Exception\SettingNotFoundException;
use KSA\Settings\Exception\SettingsException;
use KSP\Core\DTO\User\IUser;

interface IUserSettingRepository {

    /**
     * @param UserSetting $userSetting
     * @return void
     * @throws SettingsException
     */
    public function add(UserSetting $userSetting): void;

    /**
     * @param UserSetting $userSetting
     * @return void
     * @throws SettingNotDeletedException
     */
    public function remove(UserSetting $userSetting): void;

    /**
     * @param string $key
     * @param IUser  $user
     * @return UserSetting
     * @throws SettingNotFoundException
     * @throws SettingsException
     */
    public function get(string $key, IUser $user): UserSetting;

    public function getAll(): ArrayList;

}