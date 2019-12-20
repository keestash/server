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

use Keestash;
use Keestash\Core\Permission\PermissionFactory;
use Keestash\Core\Service\File\FileService;
use Keestash\Core\Service\File\RawFile\RawFileService;
use Keestash\Core\Service\UserService;
use Keestash\View\Navigation\Part;
use KSA\Account\Application\Application;
use KSP\Core\Controller\AppController;
use KSP\Core\DTO\IUser;
use KSP\Core\Manager\FileManager\IFileManager;
use KSP\Core\Manager\TemplateManager\ITemplateManager;
use KSP\Core\Repository\Permission\IPermissionRepository;
use KSP\L10N\IL10N;

class AccountController extends AppController {

    public const ACCOUNT_ROUTE_ID  = 1;
    public const SECURITY_ROUTE_ID = 2;

    private $l10n              = null;
    private $templateManager   = null;
    private $id                = null;
    private $user              = null;
    private $userService       = null;
    private $permissionManager = null;
    private $fileService       = null;
    private $fileManager       = null;
    private $rawFileService    = null;

    public function __construct(
        ITemplateManager $templateManager
        , IL10N $l10n
        , IUser $user
        , UserService $userService
        , IPermissionRepository $permissionManager
        , FileService $fileService
        , IFileManager $fileManager
        , RawFileService $rawFileService
    ) {
        $this->l10n              = $l10n;
        $this->templateManager   = $templateManager;
        $this->user              = $user;
        $this->userService       = $userService;
        $this->id                = AccountController::ACCOUNT_ROUTE_ID;
        $this->permissionManager = $permissionManager;
        $this->fileService       = $fileService;
        $this->fileManager       = $fileManager;
        $this->rawFileService    = $rawFileService;

        parent::__construct(
            $templateManager
            , $l10n
        );
    }

    public function onCreate(...$params): void {

        parent::setPermission(
            PermissionFactory::getDefaultPermission()
        );

    }

    public function create(): void {
        parent::setAppNavigationTitle($this->l10n->translate("Account"));
        parent::setAppContentTitle($this->l10n->translate("Edit your Info here"));

        parent::addAppNavigation(
            $this->getPart(
                $this->l10n->translate("Personal Information")
                , AccountController::ACCOUNT_ROUTE_ID)
        );

        parent::addAppNavigation(
            $this->getPart(
                $this->l10n->translate("Security")
                , AccountController::SECURITY_ROUTE_ID)
        );

        $content = "";

        if (AccountController::ACCOUNT_ROUTE_ID === $this->id) {

            $info = new PersonalInfo(
                $this->templateManager
                , $this->l10n
                , $this->user
                , $this->userService
                , $this->fileManager
                , $this->rawFileService
                , $this->fileService
            );

            $content = $info->handle();
        } else {
            if (AccountController::SECURITY_ROUTE_ID === $this->id) {

                $security = new Security(
                    $this->templateManager
                    , $this->l10n
                    , $this->user
                );

                $content = $security->handle();

            }
        }


        parent::setAppContent($content);
    }

    private function getPart(string $name, int $id) {
        $x = new Part();
        $x->setId($id);
        $x->setName($name);
        $x->setColorCode("");
        $x->setHref(Keestash::getBaseURL() . "/" . Application::APP_ID . "/list/" . $id . "/");
        return $x;
    }

    public function afterCreate(): void {
    }

}