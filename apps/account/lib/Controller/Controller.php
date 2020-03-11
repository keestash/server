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

namespace KSA\Account\Controller;

use Keestash;
use Keestash\View\Navigation\Part;
use KSA\Account\Application\Application;
use KSA\Account\Exception\ControllerNotFoundException;
use KSP\Core\Controller\AppController;

abstract class Controller extends AppController {

    public const CONTROLLER_PERSONAL_INFORMATION = 1;
    public const CONTROLLER_SECURITY             = 2;

    public function onCreate(...$params): void {
        parent::addAppNavigation(
            $this->getPart(
                $this->getL10N()->translate("Personal Information")
                , Controller::CONTROLLER_PERSONAL_INFORMATION
            )
        );

        parent::addAppNavigation(
            $this->getPart(
                $this->getL10N()->translate("Security")
                , Controller::CONTROLLER_SECURITY
            )
        );

    }

    private function getPart(string $name, int $id) {
        $x = new Part();
        $x->setId($id);
        $x->setName($name);

        switch ($id) {
            case Controller::CONTROLLER_PERSONAL_INFORMATION:
                $x->setHref(
                    Keestash::getBaseURL() . "/" . Application::ACCOUNT_PERSONAL_INFORMATION
                );
                break;
            case Controller::CONTROLLER_SECURITY:
                $x->setHref(
                    Keestash::getBaseURL() . "/" . Application::ACCOUNT_SECURITY
                );
                break;
            default:
                throw new ControllerNotFoundException();
        }

        return $x;
    }

}