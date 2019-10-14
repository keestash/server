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
use KSP\Core\Controller\AppController;
use KSP\Core\Manager\TemplateManager\ITemplateManager;
use KSP\Core\Repository\Permission\IPermissionRepository;
use KSP\L10N\IL10N;

class Controller extends AppController {

    private $permissionManager = null;
    private $templateManager   = null;

    public function __construct(
        ITemplateManager $templateManager
        , IPermissionRepository $permissionManager
        , IL10N $l10n
    ) {
        parent::__construct(
            $templateManager
            , $l10n
        );

        $this->templateManager   = $templateManager;
        $this->permissionManager = $permissionManager;
    }

    public function onCreate(...$params): void {
        parent::setPermission(
            $this->permissionManager->getPermission(Application::PERMISSION_TNC)
        );
    }

    public function create(): void {

    }

    public function afterCreate(): void {

    }

}