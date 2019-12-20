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

use Keestash;
use Keestash\Core\Manager\NavigationManager\NavigationManager;
use KSP\Core\Manager\CookieManager\ICookieManager;
use KSP\Core\Manager\SessionManager\ISessionManager;
use KSP\Core\Manager\TemplateManager\ITemplateManager;
use KSP\Core\Permission\IPermission;
use KSP\Core\View\Navigation\INavigation;
use KSP\Core\View\Navigation\IPart;
use KSP\L10N\IL10N;

abstract class AppController implements IAppController {

    /** @var null|IPermission $permission */
    private $permission = null;

    /** @var ITemplateManager|null $templateManager */
    private $templateManager = null;

    /** @var int $controllerType */
    private $controllerType = IAppController::CONTROLLER_TYPE_NORMAL;

    /** @var IL10N $l10n */
    private $l10n = null;

    public function __construct(
        ITemplateManager $templateManager
        , IL10N $l10n
    ) {
        $this->templateManager = $templateManager;
        $this->l10n            = $l10n;
    }

    public function setAppNavigationTitle(string $title): void {
        if (false === $this->isAppNavigationVisible()) return;
        $this->templateManager->replace("app-navigation.html",
            ["navigationTitle" => $title]
        );
    }

    public function isAppNavigationVisible(): bool {
        return $this->getControllerType() === IAppController::CONTROLLER_TYPE_NORMAL;
    }

    public function getControllerType(): int {
        return $this->controllerType;
    }

    protected function setControllerType(int $controllerType): void {
        if (true === in_array($controllerType, [
                IAppController::CONTROLLER_TYPE_NORMAL
                , IAppController::CONTROLLER_TYPE_FULL_SCREEN
                , IAppController::CONTROLLER_TYPE_NO_APP_CONTEXT
            ])) {
            $this->controllerType = $controllerType;
        }
    }

    public function setHasAppNavigation(bool $hasAppNavigation): void {
        $this->templateManager->replace("app-navigation.html",
            ["hasAppNavigationInput" => $hasAppNavigation]
        );
    }

    public function setAppContentTitle(string $title): void {
        $this->templateManager->replace("content.html",
            ["appTitle" => $title]
        );
    }

    public function getPermission(): IPermission {
        return $this->permission;
    }

    protected function setPermission(IPermission $permission): void {
        $this->permission = $permission;
    }

    protected function addAppNavigation(?IPart $part): void {
        if (false === $this->isAppNavigationVisible()) return;
        if (null === $part) return;
        Keestash::getServer()
            ->getNavigationManager()
            ->getByName(NavigationManager::NAVIGATION_TYPE_APP)
            ->addPart($part);
    }

    protected function setAppNavigation(?INavigation $navigation): void {
        if (false === $this->isAppNavigationVisible()) return;
        if (null === $navigation) return;
        Keestash::getServer()
            ->getNavigationManager()
            ->getByName(NavigationManager::NAVIGATION_TYPE_APP)
            ->addAll($navigation->getAll());
    }

    protected function render(string $templateName): void {
        $this->setAppContent(
            $this->getTemplateManager()->render($templateName)
        );
    }

    protected function setAppContent(string $content): void {
        $this->templateManager->replace("app-content.html",
            ["appContent" => $content]
        );
    }

    protected function getCookieManager(): ICookieManager {
        return Keestash::getServer()->query(ICookieManager::class);
    }

    protected function getSessionManager(): ISessionManager {
        return Keestash::getServer()->query(ISessionManager::class);
    }

    protected function getTemplateManager(): ITemplateManager {
        return $this->templateManager;
    }

    protected function getL10N(): IL10N {
        return $this->l10n;
    }

}