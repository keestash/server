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

namespace KSA\Users\Controller;

use KSP\Core\Controller\AppController;
use KSP\Core\Repository\User\IUserRepository;
use KSP\Core\Service\Controller\IAppRenderer;
use KSP\L10N\IL10N;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Http\Message\ServerRequestInterface;

class UsersController extends AppController {

    public const ALL_USERS = 1;

    private IL10N                     $l10n;
    private TemplateRendererInterface $templateRenderer;
    private IUserRepository           $userRepository;

    public function __construct(
        TemplateRendererInterface $templateRenderer
        , IL10N $l10n
        , IUserRepository $userRepository
        , IAppRenderer $appRenderer
    ) {
        parent::__construct($appRenderer);

        $this->l10n             = $l10n;
        $this->templateRenderer = $templateRenderer;
        $this->userRepository   = $userRepository;
    }

    public function run(ServerRequestInterface $request): string {
        return $this->templateRenderer
            ->render(
                "users::users"
                , [
                    "name"                        => $this->l10n->translate("Name")
                    , "firstName"                 => $this->l10n->translate("First Name")
                    , "lastName"                  => $this->l10n->translate("Last Name")
                    , "hash"                      => $this->l10n->translate("Hash")
                    , "email"                     => $this->l10n->translate("Email")
                    , "website"                   => $this->l10n->translate("Website")
                    , "password"                  => $this->l10n->translate("Password")
                    , "addNewPasswordPlaceholder" => $this->l10n->translate("New Password")
                    , "phone"                     => $this->l10n->translate("Phone")
                    , "registerDate"              => $this->l10n->translate("RegistrationDate")
                    , "delete"                    => $this->l10n->translate("Delete")
                    , "lock"                      => $this->l10n->translate("Lock")
                    , "lockedInfo"                => $this->l10n->translate("This Account is locked")
                    , "deletedInfo"               => $this->l10n->translate("This Account is marked for deletion and will be completly removed from the system once the deadline is reached")
                    , "addUser"                   => $this->l10n->translate("Add User")
                    , "users"                     => $this->userRepository->getAll()
                ]
            );

    }

}
