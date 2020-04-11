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

use KSP\Core\DTO\User\IUser;
use KSP\Core\Manager\TemplateManager\ITemplateManager;
use KSP\L10N\IL10N;

class Security {

    private $templateManager = null;
    private $l10n            = null;
    private $user            = null;

    public function __construct(
        ITemplateManager $templateManager
        , IL10N $l10n
        , IUser $user
    ) {
        $this->templateManager = $templateManager;
        $this->l10n            = $l10n;
        $this->user            = $user;
    }

    public function handle() {
        $this->templateManager->replace("security.html",
            [
                "security"                      => $this->l10n->translate("Security")
                , "currentPasssword"            => $this->l10n->translate("Current Login")
                , "currentPassswordPlaceholder" => $this->l10n->translate("Current Login")
                , "password"                    => $this->l10n->translate("Login")
                , "passwordRepeat"              => $this->l10n->translate("Login Repeat")
                , "passwordPlaceholder"         => $this->l10n->translate("Login")
                , "passwordRepeatPlaceholder"   => $this->l10n->translate("Login Repeat")
                , "save"                        => $this->l10n->translate("Save")
                , "userId"                      => $this->user->getId()

            ]
        );
        return $this->templateManager->render("security.html");
    }

}
