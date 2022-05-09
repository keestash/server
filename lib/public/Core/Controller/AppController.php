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

namespace KSP\Core\Controller;

use Keestash\View\ActionBar\ActionBar\NullActionBar;
use Keestash\View\Navigation\App\NavigationList;
use KSP\Core\Service\Controller\IAppRenderer;
use KSP\Core\View\ActionBar\IActionBar;
use Laminas\Diactoros\Response\HtmlResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

abstract class AppController implements IAppController, RequestHandlerInterface {

    private IAppRenderer $appRenderer;

    private NavigationList $navigationList;
    private IActionBar     $actionBar;

    private bool $hasGlobalSearch = true;

    public function __construct(IAppRenderer $appRenderer) {
        $this->appRenderer    = $appRenderer;
        $this->navigationList = new NavigationList();
        $this->actionBar      = new NullActionBar();
    }

    public abstract function run(ServerRequestInterface $request): string;


    protected function setAppNavigation(NavigationList $navigationList): void {
        $this->navigationList = $navigationList;
    }

    protected function setActionBar(IActionBar $actionBar): void {
        $this->actionBar = $actionBar;
    }

    protected function deactivateGlobalSearch(): void {
        $this->hasGlobalSearch = false;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface {
        // it is important to run "run" at the very beginning
        // in order to get the navigation list and maybe
        // other stuff in the future
        $static           = $this instanceof StaticAppController;
        $contextLess      = $this instanceof ContextLessAppController;
        $content          = $this->run($request);
        $hasAppNavigation = $this->navigationList->length() > 0;
        return new HtmlResponse(
            $this->appRenderer->render(
                $request
                , $hasAppNavigation
                , $content
                , $static
                , $contextLess
                , $this->navigationList
                , $this->actionBar
                , static::class
                , $this->hasGlobalSearch
            )
        );
    }

}
