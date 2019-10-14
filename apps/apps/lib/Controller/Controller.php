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

namespace KSA\Apps\Controller;

use doganoo\PHPAlgorithms\Datastructure\Lists\ArrayLists\ArrayList;
use KSA\Apps\Application\Application;
use KSP\Core\Controller\AppController;
use KSP\Core\Manager\TemplateManager\ITemplateManager;
use KSP\Core\Repository\AppRepository\IAppRepository;
use KSP\Core\Repository\Permission\IPermissionRepository;
use KSP\L10N\IL10N;

class Controller extends AppController {

    public const TEMPLATE_NAME_APPS = "apps.twig";

    private $permissionRepository = null;
    private $appRepository        = null;

    public function __construct(
        ITemplateManager $templateManager
        , IL10N $l10n
        , IAppRepository $appRepository
        , IPermissionRepository $permissionRepository
    ) {
        parent::__construct(
            $templateManager
            , $l10n
        );

        $this->appRepository        = $appRepository;
        $this->permissionRepository = $permissionRepository;
    }

    public function onCreate(...$params): void {

        parent::setPermission(
            $this->permissionRepository->getPermission(Application::PERMISSION_READ_APPS)
        );

    }

    public function create(): void {

        $apps   = $this->appRepository->getAllApps();
        $result = new ArrayList();
        foreach ($apps->keySet() as $key) {
            $result->add($apps->get($key));
        }

        parent::getTemplateManager()->replace(
            Controller::TEMPLATE_NAME_APPS
            , [
                "appId"      => $this->getL10N()->translate("App Id")
                , "enabled"  => $this->getL10N()->translate("Active")
                , "version"  => $this->getL10N()->translate("Version")
                , "createTs" => $this->getL10N()->translate("CreateTs")
                , "apps"     => $result
            ]
        );

        parent::render(Controller::TEMPLATE_NAME_APPS);
    }


    public function afterCreate(): void {

    }

}