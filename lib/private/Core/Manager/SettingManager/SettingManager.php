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

namespace Keestash\Core\Manager\SettingManager;

use doganoo\PHPAlgorithms\Datastructure\Table\HashTable;
use KSP\Core\DTO\Setting\IContext;
use KSP\Core\DTO\Setting\ISetting;
use KSP\Core\Manager\SettingManager\ISettingManager;

class SettingManager implements ISettingManager {

    private HashTable $settings;

    public function __construct() {
        $this->settings = new HashTable();
    }

    public function addSetting(IContext $context, ISetting $setting): void {
        $list = $this->settings->get($context);
        if (null === $list) {
            $list = [];
        }
        $list[] = $setting;
        $this->settings->put($context, $list);
    }

    public function getSettings(): HashTable {
        return $this->settings;
    }

}