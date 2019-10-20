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

namespace KSP\Core\View\Navigation\Factory;

use Keestash\Core\DTO\SettingEntry;
use KSP\Core\View\Navigation\IEntry;

/**
 * Class NavigationFactory
 * @package KSP\Core\View\Navigation\Factory
 * TODO move class!
 */
class NavigationFactory {

    private function __construct() {

    }

    public static function createEntry(
        string $id
        , string $name
        , int $startDate
        , int $endDate
        , string $faClass
        , int $order
    ): IEntry {
        $settingEntry = new SettingEntry();
        $settingEntry->setId($id);
        $settingEntry->setName($name);
        $settingEntry->setStartDate($startDate);
        $settingEntry->setEndDate($endDate);
        $settingEntry->setFaClass($faClass);
        $settingEntry->setOrder($order);
        return $settingEntry;
    }

}