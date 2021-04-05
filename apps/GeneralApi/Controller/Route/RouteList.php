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

namespace KSA\GeneralApi\Controller\Route;

use KSP\App\IApp;
use KSP\Core\Controller\AppController;
use KSP\Core\Service\Controller\IAppRenderer;
use Laminas\Config\Config;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Http\Message\ServerRequestInterface;

class RouteList extends AppController {

    private TemplateRendererInterface $templateRenderer;
    private Config                    $config;

    public function __construct(
        IAppRenderer $appRenderer
        , TemplateRendererInterface $templateRenderer
        , Config $config
    ) {
        parent::__construct($appRenderer);

        $this->templateRenderer = $templateRenderer;
        $this->config           = $config;
    }

    public function run(ServerRequestInterface $request): string {

        return $this->templateRenderer
            ->render(
                "generalApi::route_list"
                , [
                    "httpRoutes"  => $this->config->get(IApp::CONFIG_PROVIDER_WEB_ROUTER)->get(IApp::CONFIG_PROVIDER_ROUTES)->toArray()
                    , "apiRoutes" => $this->config->get(IApp::CONFIG_PROVIDER_API_ROUTER)->get(IApp::CONFIG_PROVIDER_ROUTES)->toArray()
                ]
            );

    }

}
