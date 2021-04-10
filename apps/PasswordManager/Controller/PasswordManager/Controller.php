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

use Keestash\View\ActionBar\ActionBarBuilder;
use KSA\PasswordManager\Controller\PasswordManager\Segment\Helper as SegmentHelper;
use KSP\Core\Controller\AppController;
use KSP\Core\Service\Controller\IAppRenderer;
use KSP\Core\View\ActionBar\IActionBar;
use KSP\Core\View\ActionBar\IElement;
use KSP\L10N\IL10N;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class Controller
 *
 * @package KSA\PasswordManager\Controller
 *
 */
class Controller extends AppController {

    private TemplateRendererInterface $templateRenderer;
    private IL10N                     $translator;

    public function __construct(
        TemplateRendererInterface $templateRenderer
        , IL10N $translator
        , IAppRenderer $appRenderer
    ) {
        parent::__construct($appRenderer);
        $this->translator       = $translator;
        $this->templateRenderer = $templateRenderer;
    }

    private function buildAppNavigation(): void {
        $segmentHelper = new SegmentHelper($this->translator);

        $this->buildActionBar();
        $this->setAppNavigation(
            $segmentHelper->buildAppNavigation()
        );
    }

    private function buildActionBar(): void {
        $actionBarBuilder = new ActionBarBuilder(IActionBar::TYPE_PLUS);
        $actionBarBuilder
            ->withElement(
                $this->translator->translate("New Password")
                , "pwm__new__password"
                , null
                , IElement::TYPE_KEY
            )
            ->withElement(
                $this->translator->translate("New Folder")
                , "pwm__new__folder"
                , null
                , IElement::TYPE_FOLDER
            )
            ->withId("password__manager__add")
            ->withDescription(
                $this->translator->translate("Create")
            )
            ->build();
    }

    public function run(ServerRequestInterface $request): string {
        $this->buildAppNavigation();
        $this->buildActionBar();
        return $this->templateRenderer->render(
            'passwordManager::password_manager'
            , [
                "rootId"                      => 'root'
                , "nothingClicked"            => $this->translator->translate("No Password Selected")
                , "nothingClickedDescription" => $this->translator->translate("Please select a password or create a new one")
                , "searchPasswords"           => $this->translator->translate("Search Passwords")
            ]
        );
    }

}
