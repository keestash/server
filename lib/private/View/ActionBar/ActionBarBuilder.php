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

namespace Keestash\View\ActionBar;

use doganoo\PHPAlgorithms\Datastructure\Lists\ArrayLists\ArrayList;
use Exception;
use KSP\Core\View\ActionBar\IActionBar;
use KSP\Core\View\ActionBar\IActionBarElement;

class ActionBarBuilder {

    /** @var IActionBar $actionBar */
    private $actionBar = null;
    private $elements  = null;

    public function __construct($argument) {

        if (is_string($argument)) {
            $this->createActionBar($argument);
            $this->elements = new ArrayList();
        } else {
            if ($argument instanceof IActionBar) {
                $this->actionBar = $argument;
            } else {
                throw new Exception("no arg");
            }
        }

        $this->elements = new ArrayList();

    }

    private function createActionBar(string $type): void {
        $actionBar = null;

        switch ($type) {
            case IActionBar::TYPE_PLUS:
                $actionBar = new AddActionBar();
                break;
            case IActionBar::TYPE_SETTINGS:
                $actionBar = new SettingsActionBar();
                break;
        }

        $this->actionBar = $actionBar;
    }

    public function withElement(
        string $name
        , ? string $id = null
        , ?string $href = null
    ): ActionBarBuilder {
        $element = new ActionBarElement($name);
        $element->setId($id);
        $element->setHref($href);
        $this->elements->add($element);
        return $this;
    }

    public function withName(string $name): ActionBarBuilder {
        $this->actionBar->setName($name);
        return $this;
    }

    public function build(): IActionBar {
        /** @var IActionBarElement $element */
        foreach ($this->elements as $element) {
            $this->actionBar->addElement($element);
        }
        return $this->actionBar;
    }

}