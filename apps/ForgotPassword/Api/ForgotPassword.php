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
use Keestash\Api\Response\JsonResponse;
use Keestash\Api\Response\LegacyResponse;
use Keestash\Core\Service\Email\EmailService;
use Keestash\Core\Service\HTTP\HTTPService;
use Keestash\Core\Service\User\UserService;
use Keestash\Legacy\Legacy;
use KSP\Api\IRequest;
use KSP\Api\IResponse;
use KSP\Core\DTO\User\IUser;
use KSP\Core\DTO\User\IUserState;
use KSP\Core\Repository\User\IUserRepository;
use KSP\Core\Repository\User\IUserStateRepository;
use KSP\L10N\IL10N;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ForgotPassword implements RequestHandlerInterface {

    private EmailService              $emailService;
    private Legacy                    $legacy;
    private UserService               $userService;
    private IUserStateRepository      $userStateRepository;
    private IL10N                     $translator;
    private IUserRepository           $userRepository;
    private TemplateRendererInterface $templateRenderer;
    private HTTPService               $httpService;

    public function __construct(
        EmailService                $emailService
        , Legacy                    $legacy
        , UserService               $userService
        , IUserStateRepository      $userStateRepository
        , IL10N                     $translator
        , IUserRepository           $userRepository
        , TemplateRendererInterface $templateRenderer
        , HTTPService               $httpService
    ) {
        $this->emailService        = $emailService;
        $this->legacy              = $legacy;
        $this->userService         = $userService;
        $this->userStateRepository = $userStateRepository;
        $this->translator          = $translator;
        $this->userRepository      = $userRepository;
        $this->templateRenderer    = $templateRenderer;
        $this->httpService         = $httpService;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface {

        $parameters     = json_decode((string) $request->getBody(), true);
        $input          = $parameters["input"] ?? null;
        $responseHeader = $this->translator->translate("Password reset");
        $debug          = $request->getAttribute(IRequest::ATTRIBUTE_NAME_DEBUG, false);

        if (null === $input || "" === $input) {
            return new JsonResponse(
                [
                    "header"    => $responseHeader
                    , "message" => $this->translator->translate("No parameter given")
                ]
                , IResponse::BAD_REQUEST
            );
        }

        $users       = $this->userRepository->getAll();
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
            return new JsonResponse(
                [
                    "header"    => $responseHeader
                    , "message" => $this->translator->translate("No user found")
                ]
                , IResponse::NOT_FOUND
            );
        }

        if (true === $this->userService->isDisabled($user)) {
            return new JsonResponse(
                [
                    "header"    => $responseHeader
                    , "message" => $this->translator->translate("Can not reset the user. Please contact your admin")
                ]
                , IResponse::FORBIDDEN
            );

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

            return new JsonResponse(
                [
                    "header"    => $responseHeader
                    , "message" => $this->translator->translate("You have already requested an password reset. Please check your mails or try later again")
                ]
                , IResponse::NOT_ACCEPTABLE
            );

        }
        $uuid      = StringUtil::getUUID();
        $appName   = $this->legacy->getApplication()->get("name");
        $appSlogan = $this->legacy->getApplication()->get("slogan");

        $baseUrl = $this->httpService->getBaseURL(true, true);

        $ctaLink = $baseUrl . "/reset_password/" . $uuid;

        $rendered = $this->templateRenderer->render(
            'forgotPassword::forgot_email'
            , [
                // changeable
                "appName"          => $appName
                , "logoAlt"        => $appName
                , "appSlogan"      => $appSlogan

                // TODO load this from theming
                , "bodyBackground" => "#f8f8f8"
                , "themeColor"     => "#269dff"

                // strings
                , "mailTitle"      => $this->translator->translate("Reset Password")
                , "salutation"     => $this->translator->translate("Dear {$user->getName()},")
                , "text"           => $this->translator->translate("This email was sent to {$user->getEmail()} to reset your password. If you did not request a reset, please ignore this mail or let us know.")
                , "ctaButtonText"  => $this->translator->translate("Reset Password")
                , "thanksText"     => $this->translator->translate("-Thanks $appName")
                , "poweredByText"  => $this->translator->translate("Powered By $appName")

                // values
                , "logoPath"       => $this->httpService->getBaseURL(false) . "/asset/img/logo_inverted.png"
                , "ctaLink"        => $ctaLink
                , "baseUrl"        => $baseUrl
                , "hasUnsubscribe" => false
            ]
        );

        // TODO check them
        //   make sure that there is no bot triggering a lot of mails
        $this->emailService->setBody($rendered);
        $this->emailService->setSubject($this->translator->translate("Resetting Password"));
        $this->emailService->addRecipient(
            $user->getName()
            , $user->getEmail()
        );
        $sent = $this->emailService->send();

        if (true === $sent || true === $debug) {
            $this->userStateRepository->revertPasswordChangeRequest($user);
            $this->userStateRepository->requestPasswordReset($user, $uuid);
        }

        $response = [
            "header"    => $responseHeader
            , "message" => $this->translator->translate("We sent an email to reset your password")
        ];

        if (true === $debug) {
            $response['uuid'] = $ctaLink;
        }

        return new JsonResponse(
            $response
            , IResponse::OK
        );
    }

}
