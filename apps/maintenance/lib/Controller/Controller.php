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

namespace KSA\Maintenance\Controller;

use KSP\Core\Controller\FullScreen\FullscreenAppController;
use KSP\Core\Manager\TemplateManager\ITemplate;
use KSP\Core\Manager\TemplateManager\ITemplateManager;

use KSP\L10N\IL10N;

class Controller extends FullscreenAppController {

    private $templateManager   = null;
    private $translator        = null;

    public function __construct(
        ITemplateManager $templateManager
        , IL10N $translator
    ) {
        parent::__construct(
            $templateManager
            , $translator
        );

        $this->templateManager   = $templateManager;
        $this->translator        = $translator;
    }

    public function onCreate(): void {

    }

    public function create(): void {
        $this->templateManager->replace(
            "maintenance.html",
            [
                "header"        => $this->translator->translate("Maintenance Mode")
                , "description" => $this->translator->translate("This instance is currently in maintenance mode. Please try it again later!")
                , "footerText"  => $this->translator->translate("Contact your system administrator if this page appears unexpectedly or too long.")
            ]
        );

        $string = $this->templateManager
            ->render("maintenance.html");
        $this->templateManager->replace(
            ITemplate::APP_CONTENT
            , ["appContent" => $string]
        );
    }

    public function afterCreate(): void {

    }

}
