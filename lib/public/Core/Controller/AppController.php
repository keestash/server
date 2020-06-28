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
use Keestash\Core\Manager\NavigationManager\App\NavigationManager as AppNavigationManager;
use Keestash\View\Navigation\App\NavigationList;
use KSP\Core\Manager\TemplateManager\ITemplate;
use KSP\Core\Manager\TemplateManager\ITemplateManager;
use KSP\Core\Permission\IPermission;
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
        $this->templateManager->replace(
            ITemplate::APP_NAVIGATION
            , [
                "hasAppNavigationInput" => $hasAppNavigation
            ]
        );
    }

    public function getPermission(): IPermission {
        return $this->permission;
    }

    protected function setPermission(IPermission $permission): void {
        $this->permission = $permission;
    }

    protected function setAppNavigation(NavigationList $navigationList): void {
        if (Keestash::getMode() !== Keestash::MODE_WEB) return;

        /** @var AppNavigationManager $appNavigation */
        $appNavigation = Keestash::getServer()
            ->query(AppNavigationManager::class);
        $appNavigation->setList($navigationList);
    }

    protected function render(string $templateName): void {
        $this->setAppContent(
            $this->getTemplateManager()->render($templateName)
        );
    }

    protected function setAppContent(string $content): void {
        $this->templateManager->replace(
            ITemplate::APP_CONTENT
            , [
                "appContent" => $content
            ]
        );
    }

    protected function getTemplateManager(): ITemplateManager {
        return $this->templateManager;
    }

    protected function getL10N(): IL10N {
        return $this->l10n;
    }

}
