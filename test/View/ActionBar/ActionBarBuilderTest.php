<?php
declare(strict_types=1);
/**
 * server
 *
 * Copyright (C) <2020> <Dogan Ucar>
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

namespace KST\View\ActionBar;

use Keestash\View\ActionBar\ActionBarBuilder;
use KSP\Core\View\ActionBar\IActionBar;
use KSP\Core\View\ActionBar\IElement;
use KST\TestCase;

class ActionBarBuilderTest extends TestCase {

    public function testCreateActionBar(): void {
        $actionBarBuilder = new ActionBarBuilder(IActionBar::TYPE_PLUS);
        $actionBar        = $actionBarBuilder
            ->withElement(
                "Foo"
                , "bar"
                , "to/my/home/"
                , IElement::TYPE_CIRCLE
            )
            ->withDescription("my Description")
            ->withId("myId")
            ->build();

        $this->assertInstanceOf(IActionBar::class, $actionBar);
        $this->assertTrue($actionBar->getId() === "myId");
        $this->assertTrue($actionBar->getDescription() === "my Description");
        $this->assertTrue($actionBar->getElements()->length() === 1);
        /** @var IElement $element */
        $element = $actionBar->getElements()->get(0);
        $this->assertInstanceOf(IElement::class, $element);
        $this->assertTrue($element->getDescription() === "Foo");
        $this->assertTrue($element->getId() === "bar");
        $this->assertTrue($element->getHref() === "to/my/home/");
        $this->assertTrue($element->getType() === IElement::TYPE_CIRCLE);
    }

}
