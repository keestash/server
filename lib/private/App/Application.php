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

namespace Keestash\App;

use doganoo\PHPAlgorithms\Datastructure\Table\HashTable;
use Keestash;
use Keestash\Command\KeestashCommand;
use Keestash\Core\Manager\NavigationManager\NavigationManager;
use KSP\App\IApp;
use KSP\App\IApplication;
use KSP\Core\Manager\RouterManager\IRouterManager;
use KSP\Core\View\Navigation\Factory\NavigationFactory;
use Symfony\Component\Console\Command\Command;

abstract class Application implements IApplication {

    private $app               = null;
    private $frontendTemplates = null;

    public function __construct(IApp $app) {
        $this->app               = $app;
        $this->frontendTemplates = new HashTable();
    }

    public abstract function register(): void;

    protected function addSetting(string $route, string $name, string $faClass, int $order): void {
        if (Keestash::MODE_WEB !== Keestash::getMode()) return;

        Keestash::getServer()
            ->getNavigationManager()
            ->getByName(NavigationManager::NAVIGATION_TYPE_SETTINGS)
            ->get(NavigationManager::NAVIGATION_TYPE_SETTINGS_PART_INDEX)
            ->addEntry(
                NavigationFactory::createEntry(
                    $route
                    , $name
                    , time()
                    , time()
                    , $faClass
                    , $order
                )
            );
    }

    protected function addStylesheet(string $id, string $name): void {
        if (Keestash::MODE_WEB !== Keestash::getMode()) return;
        $url = Keestash::getBaseURL(false) . "apps/" . $id;
        Keestash::getServer()->getTemplateManager()->addStylesheet($url . "/css/$name");
    }

    protected function addJavaScript(string $id): bool {
        if (Keestash::MODE_WEB !== Keestash::getMode()) return false;
        Keestash::getServer()->getTemplateManager()->addAppScript(
            "apps/$id/js/dist/$id.bundle.js"
            , $id
            , "application/javascript"
        );
        return true;
    }

    protected function addJavaScriptFor(string $appName, string $fileName, string $route): bool {
        if (Keestash::MODE_WEB !== Keestash::getMode()) return false;
        Keestash::getServer()->getTemplateManager()->addAppScript(
            "apps/$appName/js/dist/$fileName.bundle.js"
            , $route
            , "application/javascript"
        );
        return true;
    }


    protected function registerRoute(
        string $name
        , string $class
        , array $verbs = ["GET"]
    ): void {
        if (Keestash::MODE_WEB !== Keestash::getMode()) return;
        Keestash::getServer()
            ->getRouterManager()
            ->get(IRouterManager::HTTP_ROUTER)
            ->addRoute
            (
                $name
                , [
                    'controller' => $class
                ]
                , $verbs
            );
    }

    protected function registerApiRoute(string $name, string $class, array $verbs): void {
        if (Keestash::MODE_API !== Keestash::getMode()) return;
        Keestash::getServer()
            ->getRouterManager()
            ->get(IRouterManager::API_ROUTER)
            ->addRoute(
                $name
                , ["controller" => $class]
                , $verbs
            );
    }

    protected function registerCommand(KeestashCommand $command): bool {
        return Keestash::getServer()->getConsoleManager()->register($command);
    }

    protected function registerPublicRoute(string $name): bool {
        if (Keestash::MODE_WEB !== Keestash::getMode()) return false;
        return Keestash::getServer()->getRouterManager()->get(IRouterManager::HTTP_ROUTER)->registerPublicRoute($name);
    }

    protected function registerPublicApiRoute(string $name): bool {
        if (Keestash::MODE_API !== Keestash::getMode()) return false;
        return Keestash::getServer()->getRouterManager()->get(IRouterManager::API_ROUTER)->registerPublicRoute($name);
    }

    protected function addFrontendTemplate(string $name, string $value): bool {
        return $this->frontendTemplates->add($name, $value);
    }

    public function getFrontendTemplates(): HashTable {
        return $this->frontendTemplates;
    }

    protected function addString(string $name, string $value): void {
        Keestash::getServer()
            ->getFrontendStringManager()
            ->addString(
                $this->getApp()->getId()
                , $name
                , $value
            );
    }

    protected function getApp(): IApp {
        return $this->app;
    }

}
