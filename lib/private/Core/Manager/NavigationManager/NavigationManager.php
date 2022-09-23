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

namespace Keestash\Core\Manager\NavigationManager;

use doganoo\PHPAlgorithms\Datastructure\Lists\ArrayList\ArrayList;
use doganoo\PHPAlgorithms\Datastructure\Table\HashTable;
use Keestash\View\Navigation\Navigation;
use KSP\Core\Manager\NavigationManager\INavigationManager;
use KSP\Core\View\Navigation\IEntry;
use KSP\Core\View\Navigation\IPart;

class NavigationManager implements INavigationManager {

    public const NAVIGATION_TYPE_APP      = "app.type.navigation";
    public const NAVIGATION_TYPE_TOP      = "top.type.navigation";
    public const NAVIGATION_TYPE_SETTINGS = "top.type.settings";

    public const NAVIGATION_TYPE_SETTINGS_PART_INDEX = 0;

    private HashTable $navigationMap;


    public function __construct() {
        $this->navigationMap = new HashTable();
    }

    /**
     * @param string $name
     *
     * @return bool
     * @throws \doganoo\PHPAlgorithms\Common\Exception\InvalidKeyTypeException
     * @throws \doganoo\PHPAlgorithms\Common\Exception\UnsupportedKeyTypeException
     */
    public function addNavigation(string $name): bool {
        $navigation = new Navigation();

        if ($name === NavigationManager::NAVIGATION_TYPE_SETTINGS) {
            // TODO find a better way
            $navigation->addPart(new class implements IPart {

                private ArrayList $entries;

                public function __construct() {
                    $this->entries = new ArrayList();
                }

                public function getId(): int {
                    return 1;
                }

                public function getFAClass(): ?string {
                    return null;
                }

                public function getName(): string {
                    return "Settings";
                }

                public function getEntries(): ArrayList {
                    $this->entries->sort();
                    return $this->entries;
                }

                public function addEntry(IEntry $entry): void {
                    $this->entries->add($entry);
                }

                public function size(): int {
                    return 1;
                }

                public function getColorCode(): string {
                    return "";
                }

                public function getIconClass(): ?string {
                    return null;
                }

            });
        }
        return $this->navigationMap->put($name, $navigation);
    }

}
