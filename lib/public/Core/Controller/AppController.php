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
use Keestash\Core\Service\HTTP\Input\SanitizerService as InputSanitizer;
use Keestash\View\Navigation\App\NavigationList;
use KSP\Core\Manager\TemplateManager\ITemplate;
use KSP\Core\Manager\TemplateManager\ITemplateManager;
use KSP\L10N\IL10N;

abstract class AppController implements IAppController {

    private ITemplateManager $templateManager;
    private int              $controllerType      = IAppController::CONTROLLER_TYPE_NORMAL;
    private IL10N            $l10n;
    private array            $parameters;
    private bool             $parametersSanitized = false;
    private InputSanitizer   $inputSanitizer;

    public function __construct(
        ITemplateManager $templateManager
        , IL10N $l10n
    ) {
        $this->templateManager = $templateManager;
        $this->l10n            = $l10n;
        $this->setParameters([]);
        // TODO inject via constructor once you are ready to adapt all extending classes
        $this->inputSanitizer = Keestash::getServer()->query(InputSanitizer::class);
    }

    public function getControllerType(): int {
        return $this->controllerType;
    }

    protected function setControllerType(int $controllerType): void {
        if (true === in_array(
                $controllerType
                , [
                    IAppController::CONTROLLER_TYPE_NORMAL
                    , IAppController::CONTROLLER_TYPE_FULL_SCREEN
                    , IAppController::CONTROLLER_TYPE_STATIC
                    , IAppController::CONTROLLER_TYPE_CONTEXTLESS
                ]
            )
        ) {
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

    /**
     * @deprecated
     */
    protected function setPermission(): void {

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

    protected function getParameter(string $name, ?string $default = null): ?string {
        return $this->getParameters()[$name] ?? $default;
    }

    protected function getParameters(): array {
        if (false === $this->parametersSanitized) {
            $this->parameters          = $this->inputSanitizer->sanitizeAll($this->parameters);
            $this->parametersSanitized = true;
        }
        return $this->parameters;
    }

    public function setParameters(array $parameters): void {
        $this->parameters          = $parameters;
        $this->parametersSanitized = false;
    }

}
