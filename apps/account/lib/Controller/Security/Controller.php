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

namespace KSA\Account\Controller\Security;

use Keestash\Core\Permission\PermissionFactory;
use KSP\Core\DTO\User\IUser;
use KSP\Core\Manager\TemplateManager\ITemplateManager;
use KSP\L10N\IL10N;

class Controller extends \KSA\Account\Controller\Controller {

    private $user = null;

    public function __construct(
        ITemplateManager $templateManager
        , IL10N $l10n
        , IUser $user
    ) {
        parent::__construct($templateManager, $l10n);
        $this->user = $user;
    }

    public function onCreate(...$params): void {
        parent::onCreate($params);
        $this->setPermission(
            PermissionFactory::getDefaultPermission()
        );
    }

    public function create(): void {
        $this->getTemplateManager()->replace("security.html",
            [
                "security"                      => $this->getL10N()->translate("Security")
                , "currentPasssword"            => $this->getL10N()->translate("Current Login")
                , "currentPassswordPlaceholder" => $this->getL10N()->translate("Current Login")
                , "password"                    => $this->getL10N()->translate("Login")
                , "passwordRepeat"              => $this->getL10N()->translate("Login Repeat")
                , "passwordPlaceholder"         => $this->getL10N()->translate("Login")
                , "passwordRepeatPlaceholder"   => $this->getL10N()->translate("Login Repeat")
                , "save"                        => $this->getL10N()->translate("Save")
                , "userId"                      => $this->user->getId()

            ]
        );
        $this->setAppContent(
            $this->getTemplateManager()->render("security.html")
        );
    }

    public function afterCreate(): void {

    }

}
