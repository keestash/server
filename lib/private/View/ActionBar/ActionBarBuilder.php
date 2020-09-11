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

use doganoo\PHPAlgorithms\Datastructure\Lists\ArrayList\ArrayList;
use Exception;
use Keestash\Exception\ActionBarNotFoundException;
use Keestash\View\ActionBar\ActionBar\AddActionBar;
use Keestash\View\ActionBar\ActionBar\SettingsActionBar;
use Keestash\View\ActionBar\Element\ActionBarIElement;
use KSP\Core\View\ActionBar\IActionBar;
use KSP\Core\View\ActionBar\IElement;

/**
 * Class ActionBarBuilder
 *
 * @package Keestash\View\ActionBar
 * @author  Dogan Ucar <dogan@dogan-ucar.de>
 */
class ActionBarBuilder {

    private IActionBar $actionBar;
    private ArrayList  $elements;

    /**
     * ActionBarBuilder constructor.
     *
     * @param $argument
     *
     * @throws Exception
     */
    public function __construct($argument) {

        $this->elements = new ArrayList();
        if (is_string($argument)) {
            $this->createActionBar($argument);
        } else {
            if ($argument instanceof IActionBar) {
                $this->actionBar = $argument;
            } else {
                throw new Exception("no arg");
            }
        }

    }

    /**
     * @param string $type
     *
     * @throws ActionBarNotFoundException
     */
    private function createActionBar(string $type): void {
        $actionBar = null;

        switch ($type) {
            case IActionBar::TYPE_PLUS:
                $actionBar = new AddActionBar();
                break;
            case IActionBar::TYPE_SETTINGS:
                $actionBar = new SettingsActionBar();
                break;
            default:
                throw new ActionBarNotFoundException();
        }

        $this->actionBar = $actionBar;
    }

    public function withElement(
        string $name
        , ?string $id = null
        , ?string $href = null
        , ?string $type = IElement::TYPE_CIRCLE
    ): ActionBarBuilder {
        $element = new ActionBarIElement($name);
        $element->setId($id);
        $element->setHref($href);
        $element->setType($type);
        $this->elements->add($element);
        return $this;
    }

    public function withId(string $name): ActionBarBuilder {
        $this->actionBar->setId($name);
        return $this;
    }

    public function withDescription(string $description): ActionBarBuilder {
        $this->actionBar->setDescription($description);
        return $this;
    }

    public function build(): IActionBar {
        /** @var IElement $element */
        foreach ($this->elements as $element) {
            $this->actionBar->addElement($element);
        }
        return $this->actionBar;
    }

}
