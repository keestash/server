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

namespace KSA\Apps\Controller;

use doganoo\PHPAlgorithms\Datastructure\Lists\ArrayList\ArrayList;
use KSP\Core\Controller\AppController;
use KSP\Core\Repository\AppRepository\IAppRepository;
use KSP\Core\Service\Controller\IAppRenderer;
use KSP\L10N\IL10N;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Http\Message\ServerRequestInterface;

class Controller extends AppController {

    private TemplateRendererInterface $templateRenderer;
    private IAppRepository            $appRepository;
    private IL10N                     $translator;

    public function __construct(
        IL10N $l10n
        , IAppRepository $appRepository
        , TemplateRendererInterface $templateRenderer
        , IAppRenderer $appRenderer
    ) {
        parent::__construct($appRenderer);

        $this->appRepository    = $appRepository;
        $this->templateRenderer = $templateRenderer;
        $this->translator       = $l10n;
    }

    public function run(ServerRequestInterface $request): string {

        $apps   = $this->appRepository->getAllApps();
        $result = new ArrayList();
        foreach ($apps->keySet() as $key) {
            $result->add($apps->get($key));
        }

        return $this->templateRenderer
            ->render('apps::apps'
                , [
                    "appId"      => $this->translator->translate("App Id")
                    , "enabled"  => $this->translator->translate("Active")
                    , "version"  => $this->translator->translate("Version")
                    , "createTs" => $this->translator->translate("CreateTs")
                    , "apps"     => $result
                ]
            );
    }

}
