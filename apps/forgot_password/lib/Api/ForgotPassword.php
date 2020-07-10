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

use DateTime;
use doganoo\PHPUtil\Datatype\StringClass;
use doganoo\PHPUtil\Util\StringUtil;
use Keestash;
use Keestash\Api\AbstractApi;
use Keestash\Api\Response\DefaultResponse;
use Keestash\Core\DTO\HTTP;
use Keestash\Core\Service\Email\EmailService;
use Keestash\Core\Service\User\UserService;
use Keestash\Legacy\Legacy;
use KSA\ForgotPassword\Application\Application;
use KSP\Api\IResponse;
use KSP\Core\DTO\IToken;
use KSP\Core\DTO\User\IUser;
use KSP\Core\DTO\User\IUserState;
use KSP\Core\Manager\TemplateManager\ITemplateManager;
use KSP\Core\Repository\Permission\IPermissionRepository;
use KSP\Core\Repository\User\IUserStateRepository;
use KSP\L10N\IL10N;

class ForgotPassword extends AbstractApi {

    private const FORGOT_EMAIL_TEMPLATE_NAME = "forgot_email.twig";

    /** @var EmailService */
    private $emailService;
    /** @var ITemplateManager */
    private $templateManager;
    /** @var Legacy */
    private $legacy;
    /** @var IPermissionRepository */
    private $permissionManager;
    /** @var UserService */
    private $userService;
    /** @var IUserStateRepository */
    private $userStateRepository;

    public function __construct(
        IL10N $l10n
        , EmailService $emailService
        , ITemplateManager $templateManager
        , Legacy $legacy
        , IPermissionRepository $permissionManager
        , UserService $userService
        , IUserStateRepository $userStateRepository
        , ?IToken $token = null
    ) {
        parent::__construct($l10n, $token);

        $this->emailService        = $emailService;
        $this->legacy              = $legacy;
        $this->permissionManager   = $permissionManager;
        $this->userService         = $userService;
        $this->templateManager     = $templateManager;
        $this->userStateRepository = $userStateRepository;
    }

    public function onCreate(array $parameters): void {
        parent::setPermission(
            $this->permissionManager->getPermission(Application::PERMISSION_FORGOT_PASSWORD_SUBMIT)
        );
    }

    public function create(): void {

        $input    = $this->getParameter("input", null);
        $response = new DefaultResponse();
        $response->setCode(HTTP::OK);
        $responseHeader = $this->getL10N()->translate("Password reset");

        if (null === $input || "" === $input) {
            $response->addMessage(
                IResponse::RESPONSE_CODE_NOT_OK
                , [
                    "header"    => $responseHeader
                    , "message" => $this->getL10N()->translate("No parameter given")
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

        if (null === $user) {

            $response->addMessage(
                IResponse::RESPONSE_CODE_NOT_OK
                , [
                    "header"    => $responseHeader
                    , "message" => $this->getL10N()->translate("No user found")
                ]
            );
            $this->setResponse($response);
            return;

        }
        if (true === $this->userService->isDisabled($user)) {

            $response->addMessage(
                IResponse::RESPONSE_CODE_NOT_OK
                , [
                    "header"    => $responseHeader
                    , "message" => $this->getL10N()->translate("Can not reset the user. Please contact your admin")
                ]
            );
            $this->setResponse($response);
            return;

        }

        $userStates       = $this->userStateRepository->getUsersWithPasswordResetRequest();
        $alreadyRequested = false;

        foreach ($userStates->keySet() as $userStateId) {
            /** @var IUserState $userState */
            $userState = $userStates->get($userStateId);
            if ($user->getId() === $userState->getUser()->getId()) {
                $difference       = $userState->getCreateTs()->diff(new DateTime());
                $alreadyRequested = $difference->i < 2; // not requested within the last 2 minutes
            }
        }

        if (true === $alreadyRequested) {

            $response->addMessage(
                IResponse::RESPONSE_CODE_NOT_OK
                , [
                    "header"    => $responseHeader
                    , "message" => $this->getL10N()->translate("You have already requested an password reset. Please check your mails or try later again")
                ]
            );
            $this->setResponse($response);
            return;

        }
        $uuid      = StringUtil::getUUID();
        $appName   = $this->legacy->getApplication()->get("name");
        $appSlogan = $this->legacy->getApplication()->get("slogan");

        $baseUrl = Keestash::getBaseURL(true, true);

        $resetPassword = str_replace(
            "{token}"
            , $uuid
            , Application::RESET_PASSWORD
        );
        $ctaLink       = $baseUrl . "/" . $resetPassword;

        $this->templateManager->replace(
            ForgotPassword::FORGOT_EMAIL_TEMPLATE_NAME
            , [
                // changeable
                "appName"          => $appName
                , "logoAlt"        => $appName
                , "appSlogan"      => $appSlogan

                // TODO load this from theming
                , "bodyBackground" => "#f8f8f8"
                , "themeColor"     => "#269dff"

                // strings
                , "mailTitle"      => $this->getL10N()->translate("Reset Password")
                , "salutation"     => $this->getL10N()->translate("Dear {$user->getName()},")
                , "text"           => $this->getL10N()->translate("This email was sent to {$user->getEmail()} to reset your password. If you did not request a reset, please ignore this mail or let us know.")
                , "ctaButtonText"  => $this->getL10N()->translate("Reset Password")
                , "thanksText"     => $this->getL10N()->translate("-Thanks $appName")
                , "poweredByText"  => $this->getL10N()->translate("Powered By $appName")

                // values
                , "logoPath"       => Keestash::getBaseURL(false) . "/asset/img/logo_inverted.png"
                , "ctaLink"        => $ctaLink
                , "baseUrl"        => $baseUrl
                , "hasUnsubscribe" => false
            ]
        );


        $rendered = $this->templateManager->render(ForgotPassword::FORGOT_EMAIL_TEMPLATE_NAME);


        // TODO check them
        //   make sure that there is no bot triggering a lot of mails
        $this->emailService->setBody($rendered);
        $this->emailService->setSubject($this->getL10N()->translate("Resetting Password"));
        $this->emailService->addRecipent(
            $user->getName()
            , $user->getEmail()
        );
        $sent = $this->emailService->send();

        if (true === $sent) {
            $this->userStateRepository->revertPasswordChangeRequest($user);
            $this->userStateRepository->requestPasswordReset($user, $uuid);
        }

        $response->addMessage(
            IResponse::RESPONSE_CODE_OK
            , [
                "header"    => $responseHeader
                , "message" => $this->getL10N()->translate("We sent an email to reset your password")
            ]
        );
        $this->setResponse($response);
        return;
    }

    public function afterCreate(): void {

    }

}
