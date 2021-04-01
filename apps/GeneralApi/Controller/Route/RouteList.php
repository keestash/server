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

use Keestash;

use KSP\Core\Controller\AppController;

class RouteList extends AppController {

    public function onCreate(): void {

    }

    public function create(): void {
        $httpRouter = Keestash::getServer()->getHTTPRouter();
        $apiRouter  = Keestash::getServer()->getApiRouter();

        $this->getTemplateManager()
            ->replace(
                "route_list.twig"
                , [
                    "httpRoutes"  => $httpRouter->getRoutes()->toArray()
                    , "apiRoutes" => $apiRouter->getRoutes()->toArray()
                ]
            );

        $this->setAppContent(
            $this->getTemplateManager()
                ->render("route_list.twig")
        );
    }

    public function afterCreate(): void {

    }

}
