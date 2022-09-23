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

namespace KSP\Core\Service\Controller;

use Keestash\View\Navigation\App\NavigationList;
use KSP\Core\View\ActionBar\IActionBar;
use Psr\Http\Message\ServerRequestInterface;

interface IAppRenderer {

    public function render(
        ServerRequestInterface $request
        , bool $hasAppNavigation
        , string $appContent
        , bool $static
        , bool $contextLess
        , NavigationList $navigationList
        , string $caller
        , bool $hasGlobalSearch
    ): string;

}