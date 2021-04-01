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

namespace KSA\PasswordManager\Controller\PasswordManager;

use Keestash;
use Keestash\View\ActionBar\ActionBarBuilder;
use KSA\PasswordManager\Application\Application;
use KSA\PasswordManager\Controller\PasswordManager\Segment\Helper as SegmentHelper;
use KSA\PasswordManager\Repository\Node\NodeRepository;
use KSP\Core\Controller\AppController;
use KSP\Core\DTO\User\IUser;
use KSP\Core\Manager\TemplateManager\ITemplateManager;

use KSP\Core\View\ActionBar\IActionBar;
use KSP\Core\View\ActionBar\IBag;
use KSP\Core\View\ActionBar\IElement;
use KSP\L10N\IL10N;

/**
 * Class Controller
 *
 * @package KSA\PasswordManager\Controller
 *
 */
class Controller extends AppController {

    public const TEMPLATE_NAME = "password_manager.twig";

    private IUser                 $user;
    private NodeRepository        $nodeRepository;

    public function __construct(
        ITemplateManager $templateManager
        , IUser $user
        , IL10N $translator
        , NodeRepository $nodeRepository
    ) {
        parent::__construct(
            $templateManager
            , $translator
        );

        $this->user                 = $user;
        $this->nodeRepository       = $nodeRepository;
    }

    public function onCreate(): void {

        parent::setHasAppNavigation(false);
        $segmentHelper = new SegmentHelper($this->getL10N());

        $this->buildActionBar();
        $this->setAppNavigation(
            $segmentHelper->buildAppNavigation()
        );

    }

    private function buildActionBar(): void {
        $actionBarBag = Keestash::getServer()->getActionBarManager()->get(IBag::ACTION_BAR_TOP);
        $actionBar    = $actionBarBag->get(IActionBar::TYPE_PLUS);

        $actionBarBuilder = new ActionBarBuilder($actionBar);
        $actionBarBuilder
            ->withElement(
                $this->getL10N()->translate("New Password")
                , "pwm__new__password"
                , null
                , IElement::TYPE_KEY
            )
            ->withElement(
                $this->getL10N()->translate("New Folder")
                , "pwm__new__folder"
                , null
                , IElement::TYPE_FOLDER
            )
            ->withId("password__manager__add")
            ->withDescription(
                $this->getL10N()->translate("Create")
            )
            ->build();
    }

    public function create(): void {

        $this->getTemplateManager()->replace(
            Controller::TEMPLATE_NAME
            , [
                "rootId"                      => Application::ROOT_FOLDER
                , "nothingClicked"            => $this->getL10N()->translate("No Password Selected")
                , "nothingClickedDescription" => $this->getL10N()->translate("Please select a password or create a new one")
                , "searchPasswords"           => $this->getL10N()->translate("Search Passwords")
            ]
        );

        $string = $this->getTemplateManager()->render(Controller::TEMPLATE_NAME);
        parent::setAppContent($string);
    }

    public function afterCreate(): void {

    }

}
