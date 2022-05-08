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

namespace KSA\Settings\Controller;


use Keestash\View\Navigation\App\NavigationList;
use KSA\Settings\Service\SegmentService;
use KSA\Settings\Service\SettingService;
use KSP\Core\Controller\AppController;
use KSP\Core\DTO\Setting\IContext;
use KSP\Core\Manager\SettingManager\ISettingManager;
use KSP\Core\Service\Controller\IAppRenderer;
use KSP\L10N\IL10N;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Http\Message\ServerRequestInterface;

class Controller extends AppController {

    private TemplateRendererInterface $templateRenderer;
    private SegmentService            $segmentService;

    public function __construct(
        TemplateRendererInterface $templateRenderer
        , IAppRenderer            $appRenderer
        , SegmentService          $segmentService
    ) {
        parent::__construct($appRenderer);

        $this->templateRenderer = $templateRenderer;
        $this->segmentService   = $segmentService;
    }

    public function run(ServerRequestInterface $request): string {
        $this->buildAppNavigation();
        return $this->templateRenderer->render('settings::settings', []);
    }

    private function buildAppNavigation(): void {
        $this->setAppNavigation(
            $this->segmentService->buildAppNavigation()
        );
    }

}