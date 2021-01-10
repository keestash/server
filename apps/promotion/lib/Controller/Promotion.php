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

namespace KSA\Promotion\Controller;

use Keestash\Legacy\Legacy;
use KSA\Promotion\Application\Application;
use KSP\Core\Controller\FullScreen\FullscreenAppController;
use KSP\Core\Manager\TemplateManager\ITemplateManager;
use KSP\Core\Repository\Permission\IPermissionRepository;
use KSP\L10N\IL10N;

class Promotion extends FullscreenAppController {

    private $templateManager   = null;
    private $l10n              = null;
    private $legacy            = null;
    private $permissionManager = null;

    public function __construct(
        ITemplateManager $templateManager
        , IL10N $l10n
        , Legacy $legacy
        , IPermissionRepository $permissionManager
    ) {
        parent::__construct(
            $templateManager
            , $l10n
        );

        $this->templateManager   = $templateManager;
        $this->l10n              = $l10n;
        $this->legacy            = $legacy;
        $this->permissionManager = $permissionManager;
    }

    public function onCreate(): void {
        parent::setPermission(
            $this->permissionManager->getPermission(Application::PERMISSION_PROMOTION)
        );
    }

    public function create(): void {
        $this->templateManager->replace("promotion.html",
            [
                "doYouLikeTheApp"         => $this->l10n->translate("Do You Like " . $this->legacy->getApplication()->get("name") . "?")
                , "doYouLikeTheAppSlogan" => $this->l10n->translate("Do You Like " . $this->legacy->getApplication()->get("name") . "? Tell it to other's!")
                , "facebook"              => $this->l10n->translate("Facebook")
                , "facebookLink"          => $this->legacy->getApplication()->get("facebookPage")
                , "likeUsOn"              => $this->l10n->translate("Like us on")
                , "twitter"               => $this->l10n->translate("Twitter")
                , "twitterLink"           => $this->legacy->getApplication()->get("twitterPage")
                , "linkedIn"              => $this->l10n->translate("LinkedIn")
                , "linkedInLink"          => $this->legacy->getApplication()->get("linkedInPage")
            ]);

        parent::setAppContent($this->templateManager->render("promotion.html"));
    }

    public function afterCreate(): void {

    }

}
