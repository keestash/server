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

namespace KSA\Logout\Controller;

use doganoo\PHPUtil\HTTP\Session;
use Keestash;
use Keestash\Core\Manager\RouterManager\RouterManager;
use Keestash\Core\Manager\SessionManager\SessionManager;
use KSA\Logout\Application\Application;
use KSP\Core\Controller\StaticAppController;
use KSP\Core\Manager\TemplateManager\ITemplateManager;
use KSP\Core\Repository\Permission\IPermissionRepository;
use KSP\L10N\IL10N;

class Logout extends StaticAppController {

    private $session           = null;
    private $permissionManager = null;

    public function __construct(
        ITemplateManager $templateManager
        , SessionManager $session
        , IPermissionRepository $permissionManager
        , IL10N $l10n
    ) {
        parent::__construct(
            $templateManager
            , $l10n
        );
        $this->session           = $session;
        $this->permissionManager = $permissionManager;
    }

    public function onCreate(...$params): void {
        parent::setPermission(
            $this->permissionManager->getPermission(Application::PERMISSION_LOGOUT)
        );
    }

    public function create(): void {
        $this->session->killAll();
    }

    public function afterCreate(): void {
        Keestash::getServer()
            ->getRouterManager()
            ->get(RouterManager::HTTP_ROUTER)
            ->routeTo(\KSA\Login\Application\Application::LOGIN);
    }

}