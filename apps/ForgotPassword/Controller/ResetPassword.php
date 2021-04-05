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
use Keestash\Core\Service\HTTP\HTTPService;
use KSA\ForgotPassword\Application\Application;
use KSP\Core\Controller\StaticAppController;
use KSP\Core\DTO\User\IUserState;
use KSP\Core\Repository\User\IUserStateRepository;
use KSP\Core\Service\Controller\IAppRenderer;
use KSP\L10N\IL10N;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Http\Message\ServerRequestInterface;

class ResetPassword extends StaticAppController {

    private IUserStateRepository      $userStateRepository;
    private TemplateRendererInterface $templateRenderer;
    private IL10N                     $translator;
    private HTTPService               $httpService;

    public function __construct(
        IL10N $il10n
        , IUserStateRepository $userStateRepository
        , IAppRenderer $appRenderer
        , TemplateRendererInterface $templateRenderer
        , HTTPService $httpService
    ) {
        parent::__construct($appRenderer);
        $this->userStateRepository = $userStateRepository;
        $this->templateRenderer    = $templateRenderer;
        $this->translator          = $il10n;
        $this->httpService         = $httpService;
    }

    public function run(ServerRequestInterface $request): string {
        $parameters = $request->getQueryParams();
        $rendered   = null;
        $token      = $parameters["token"] ?? null;
        $user       = null;

        if (null === $token) {
            return '';
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
            return '';
        }

        return $this->templateRenderer->render(
            'forgotPassword::reset_password'
            , [
                // strings
                "title"            => $this->translator->translate("Reset password for {$user->getName()}")
                , "passwordLabel"  => $this->translator->translate("New Password")
                , "resetPassword"  => $this->translator->translate("Reset Password")
                , "noHashFound"    => $this->translator->translate("Link seems to be expired. Please request a new one")

                // values
                , "backgroundPath" => $this->httpService->getBaseURL(false) . "/asset/img/login-background.jpg"
                , "logoPath"       => $this->httpService->getBaseURL(false) . "/asset/img/logo_inverted.png"
                , "token"          => $token
                , "hasHash"        => $this->hasHash($token)

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

}
