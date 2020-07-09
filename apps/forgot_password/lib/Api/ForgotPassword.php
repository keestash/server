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

use doganoo\PHPUtil\Datatype\StringClass;
use doganoo\PHPUtil\Log\FileLogger;
use doganoo\PHPUtil\Util\StringUtil;
use Keestash;
use Keestash\Api\AbstractApi;
use Keestash\Api\Response\DefaultResponse;
use Keestash\Core\DTO\HTTP;
use Keestash\Core\Service\EmailService;
use Keestash\Core\Service\User\UserService;
use Keestash\Legacy\Legacy;
use KSA\ForgotPassword\Application\Application;
use KSP\Api\IResponse;
use KSP\Core\DTO\IToken;
use KSP\Core\DTO\User\IUser;
use KSP\Core\Manager\TemplateManager\ITemplateManager;
use KSP\Core\Repository\Permission\IPermissionRepository;
use KSP\L10N\IL10N;

class ForgotPassword extends AbstractApi {

    private const FORGOT_EMAIL_TEMPLATE_NAME = "forgot_email.twig";

    private $parameters        = null;
    private $translator        = null;
    private $emailService      = null;
    private $templateManager   = null;
    private $legacy            = null;
    private $permissionManager = null;
    private $userService       = null;

    public function __construct(
        IL10N $l10n
        , EmailService $emailService
        , ITemplateManager $templateManager
        , Legacy $legacy
        , IPermissionRepository $permissionManager
        , UserService $userService
        , ?IToken $token = null
    ) {
        parent::__construct($l10n, $token);

        $this->translator        = $l10n;
        $this->emailService      = $emailService;
        $this->templateManager   = $templateManager;
        $this->legacy            = $legacy;
        $this->permissionManager = $permissionManager;
        $this->userService       = $userService;
    }

    public function onCreate(array $parameters): void {
        $this->parameters = $parameters;
        parent::setPermission(
            $this->permissionManager->getPermission(Application::PERMISSION_FORGOT_PASSWORD_SUBMIT)
        );
    }

    public function create(): void {

        $input    = $this->getParameter("input", null);
        $response = new DefaultResponse();
        $response->setCode(HTTP::OK);
        $responseHeader = $this->translator->translate("Password reset");

        if (null === $input || "" === $input) {
            $response->addMessage(
                IResponse::RESPONSE_CODE_NOT_OK
                , [
                    "header"    => $responseHeader
                    , "message" => $this->translator->translate("No parameter given")
                ]
            );
            $this->setResponse($response);
            return;
        }

        $users       = Keestash::getServer()->getUsersFromCache();
        $user        = null;
        $inputObject = new StringClass($input);

        /** @var IUser $iUser */
        foreach ($users as $iUser) {

            if (
                $inputObject->equalsIgnoreCase($iUser->getEmail())
                || $inputObject->equalsIgnoreCase($iUser->getName())
            ) {
                $user = $iUser;
                break;
            }

        }

        if (true === $this->userService->isDisabled($user)) {

            $response->addMessage(
                IResponse::RESPONSE_CODE_NOT_OK
                , [
                    "header"    => $responseHeader
                    , "message" => $this->translator->translate("Can not reset the user. Please contact your admin")
                ]
            );
            $this->setResponse($response);
            return;

        }

        $uuid      = StringUtil::getUUID();
        $appName   = $this->legacy->getApplication()->get("name");
        $appSlogan = $this->legacy->getApplication()->get("slogan");
        $this->templateManager->replace(
            ForgotPassword::FORGOT_EMAIL_TEMPLATE_NAME
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


        $rendered = $this->templateManager->render(ForgotPassword::FORGOT_EMAIL_TEMPLATE_NAME);

        FileLogger::debug("$rendered");
        FileLogger::debug(Keestash::getBaseURL(true, true) . "/" . Application::RESET_PASSWORD . "/" . $uuid . "/");

        // TODO check them
        //   make sure that there is no bot triggering a lot of mails
        // $this->emailService->setBody($rendered);
        // $this->emailService->setSubject($this->translator->translate("Resetting Password"));
        // $this->emailService->addRecipent("Dogan Ucar", "dogan@dogan-ucar.de");
        // $this->emailService->send();

        $response->addMessage(
            IResponse::RESPONSE_CODE_OK
            , [
                "header"    => $responseHeader
                , "message" => $this->translator->translate("We sent an email to reset your password")
            ]
        );
        $this->setResponse($response);
        return;
    }

    public function afterCreate(): void {

    }

}
