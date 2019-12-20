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

namespace KSA\ForgotPassword\Api;

use Keestash;
use Keestash\Api\AbstractApi;
use Keestash\Api\Response\DefaultResponse;
use Keestash\Core\DTO\HTTP;
use Keestash\Core\Service\EmailService;
use Keestash\Legacy\Legacy;
use KSA\ForgotPassword\Application\Application;
use KSP\Api\IResponse;
use KSP\Core\DTO\IToken;
use KSP\Core\Manager\TemplateManager\ITemplateManager;
use KSP\Core\Repository\Permission\IPermissionRepository;
use KSP\Core\Repository\User\IUserRepository;
use KSP\L10N\IL10N;
use Prophecy\Util\StringUtil;

class ForgotPassword extends AbstractApi {

    private $parameters        = null;
    private $translator        = null;
    private $userManager       = null;
    private $emailService      = null;
    private $templateManager   = null;
    private $legacy            = null;
    private $permissionManager = null;

    public function __construct(
        IL10N $l10n
        , IUserRepository $userManager
        , EmailService $emailService
        , ITemplateManager $templateManager
        , Legacy $legacy
        , IPermissionRepository $permissionManager
        , ?IToken $token = null
    ) {
        parent::__construct($l10n, $token);

        $this->translator        = $l10n;
        $this->userManager       = $userManager;
        $this->emailService      = $emailService;
        $this->templateManager   = $templateManager;
        $this->legacy            = $legacy;
        $this->permissionManager = $permissionManager;
    }

    public function onCreate(array $parameters): void {
        $this->parameters = $parameters;
        parent::setPermission(
            $this->permissionManager->getPermission(Application::PERMISSION_FORGOT_PASSWORD_SUBMIT)
        );
    }

    public function create(): void {

        $usernameOrEmail = $this->parameters["username_or_email"] ?? null;
        $response        = new DefaultResponse();
        $response->setCode(HTTP::OK);

        if (null === $usernameOrEmail) {
            $response->addMessage(
                IResponse::RESPONSE_CODE_NOT_OK
                , [
                    "message" => $this->translator->translate("No parameter given")
                ]
            );
            parent::setResponse($response);
            return;
        }

        $mailUser = $this->userManager->getUserByMail((string) $usernameOrEmail);
        $nameUser = $this->userManager->getUser((string) $usernameOrEmail);

        if (null === $mailUser && null === $nameUser) {
            $response->addMessage(
                IResponse::RESPONSE_CODE_NOT_OK
                , [
                    "message" => $this->translator->translate("No User found")
                ]
            );
            parent::setResponse($response);
            return;
        }

        $uuid      = StringUtil::getUUID();
        $user      = null !== $mailUser ? $mailUser : $nameUser;
        $appName   = $this->legacy->getApplication()->get("name");
        $appSlogan = $this->legacy->getApplication()->get("slogan");
        $this->templateManager->replace(
            "forgot_email.html"
            , [
                "subject"                => $this->translator->translate("Reset Password")
                , "logoPath"             => Keestash::getBaseURL(false) . "/asset/img/logo.png"
                , "appName"              => $appName
                , "appSlogan"            => $appSlogan
                , "thisEmailIsSentToYou" => $this->translator->translate("This email was sent to {$user->getEmail()} to reset your password. If you did not request a reset, please ignore this mail or let us know.")
                , "forwardToFriend"      => $this->translator->translate("Forward to a friend")
                , "passwordReset"        => $this->translator->translate("Password Reset for $appName")
                , "callToAction"         => $this->translator->translate("Please follow the link below to reset your password")
                , "ctaButtonText"        => $this->translator->translate("Reset Password")
                , "ctaLink"              => Keestash::getBaseURL(true, true) . "/" . Application::RESET_PASSWORD . "/" . $uuid . "/"
            ]
        );


        $rendered = $this->templateManager->render("forgot_email.html");

        // TODO check them
        $this->emailService->setBody($rendered);
        $this->emailService->setSubject($this->translator->translate("Resetting Password"));
        $this->emailService->addRecipent("Dogan Ucar", "dogan@dogan-ucar.de");
        $this->emailService->send();

        $response->addMessage(
            IResponse::RESPONSE_CODE_OK
            , [
                "message" => $this->translator->translate("We sent an email to reset your password")
            ]
        );
        parent::setResponse($response);
        return;
    }

    public function afterCreate(): void {

    }

}