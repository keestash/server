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

namespace KSA\TNC\Controller;

use KSA\TNC\Application\Application;
use KSP\Core\Controller\ContextLessAppController;
use KSP\Core\Manager\TemplateManager\ITemplateManager;

use KSP\L10N\IL10N;

class Controller extends ContextLessAppController {

    private const TEMPLATE_NAME_TNC = "tnc.twig";

    public function __construct(
        ITemplateManager $templateManager
        , IL10N $translator
    ) {
        parent::__construct(
            $templateManager
            , $translator
        );
    }

    public function onCreate(): void {

    }

    public function create(): void {

        $this->getTemplateManager()
            ->replace(
                Controller::TEMPLATE_NAME_TNC
                , [

                ]
            );

        $this->setAppContent(
            $this->getTemplateManager()->render(Controller::TEMPLATE_NAME_TNC)
        );
    }

    public function afterCreate(): void {

    }

}