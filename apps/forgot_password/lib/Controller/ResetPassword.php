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

namespace KSA\ForgotPassword\Controller;

use DateTime;
use Keestash;
use KSA\ForgotPassword\Application\Application;
use KSP\Core\Controller\StaticAppController;
use KSP\Core\DTO\User\IUserState;
use KSP\Core\Manager\TemplateManager\ITemplate;
use KSP\Core\Manager\TemplateManager\ITemplateManager;
use KSP\Core\Repository\Permission\IPermissionRepository;
use KSP\Core\Repository\User\IUserStateRepository;
use KSP\L10N\IL10N;

class ResetPassword extends StaticAppController {

    private const RESET_PASSWORD_TEMPLATE_NAME = "reset_password.twig";

    /** @var IPermissionRepository */
    private $permissionManager;

    private $userStateRepository;

    public function __construct(
        ITemplateManager $templateManager
        , IL10N $il10n
        , IPermissionRepository $permissionRepository
        , IUserStateRepository $userStateRepository
    ) {
        parent::__construct(
            $templateManager
            , $il10n
        );

        $this->permissionManager   = $permissionRepository;
        $this->userStateRepository = $userStateRepository;
    }

    public function onCreate(...$params): void {
        parent::setPermission(
            $this->permissionManager->getPermission(Application::PERMISSION_RESET_PASSWORD)
        );
    }

    public function create(): void {
        $rendered = null;
        $token    = $this->getParameter("token", null);
        $user     = null;

        if (null === $token) {
            $this->render(ITemplate::ERROR);
            return;
        }

        $userStates = $this->userStateRepository->getUsersWithPasswordResetRequest();

        foreach ($userStates->keySet() as $userStateId) {
            /** @var IUserState $usersState */
            $usersState = $userStates->get($userStateId);
            if ($token === $usersState->getStateHash()) {
                $user = $usersState->getUser();
                break;
            }
        }

        if (null === $user) {
            $this->render(ITemplate::ERROR);
            return;
        }

        $this->getTemplateManager()->replace(
            ResetPassword::RESET_PASSWORD_TEMPLATE_NAME
            , [
                // strings
                "title"            => $this->getL10N()->translate("Reset password for {$user->getName()}")
                , "passwordLabel"  => $this->getL10n()->translate("New Password")
                , "resetPassword"  => $this->getL10n()->translate("Reset Password")
                , "noHashFound"    => $this->getL10N()->translate("Link seems to be expired. Please request a new one")

                // values
                , "backgroundPath" => Keestash::getBaseURL(false) . "/asset/img/login_background.jpg"
                , "logoPath"       => Keestash::getBaseURL(false) . "/asset/img/logo_inverted.png"
                , "token"          => $token
                , "hasHash"        => $this->hasHash($token)

            ]
        );

        $string = $this->getTemplateManager()
            ->render(ResetPassword::RESET_PASSWORD_TEMPLATE_NAME);
        $this->getTemplateManager()->replace(
            ITemplate::APP_CONTENT
            , [
                "appContent" => $string
            ]
        );


    }

    private function hasHash(?string $hash): bool {
        if (null === $hash) return false;
        $userStates = $this->userStateRepository->getUsersWithPasswordResetRequest();

        foreach ($userStates->keySet() as $userStateId) {
            /** @var IUserState $userState */
            $userState = $userStates->get($userStateId);

            if (
                $userState->getStateHash() === $hash
                && $userState->getCreateTs()->diff(new DateTime())->i < 2
            ) {
                return true;
            }

        }

        return false;
    }

    public function afterCreate(): void {

    }

}
