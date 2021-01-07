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

namespace KSA\Register\Api\User;

use doganoo\PHPUtil\Datatype\StringClass;
use doganoo\PHPUtil\HTTP\Code;
use Exception;
use Keestash;
use Keestash\Api\AbstractApi;
use Keestash\Api\Response\DefaultResponse;
use Keestash\Core\Permission\PermissionFactory;
use Keestash\Core\Service\User\UserService;
use KSA\Register\Application\Application;
use KSP\Api\IResponse;
use KSP\App\ILoader;
use KSP\Core\DTO\Token\IToken;
use KSP\Core\DTO\User\IUser;
use KSP\Core\ILogger\ILogger;
use KSP\Core\Repository\Permission\IPermissionRepository;
use KSP\Core\Repository\User\IUserRepository;
use KSP\Core\Repository\User\IUserStateRepository;
use KSP\L10N\IL10N;

class Add extends AbstractApi {

    private ?IUser                $user;
    private UserService           $userService;
    private IUserRepository       $userRepository;
    private IL10N                 $translator;
    private IPermissionRepository $permissionRepository;
    private ILoader               $loader;
    private IUserStateRepository  $userStateRepository;
    private ILogger               $logger;

    public function __construct(
        IL10N $l10n
        , UserService $userService
        , IUserRepository $userRepository
        , IPermissionRepository $permissionRepository
        , ILoader $loader
        , IUserStateRepository $userStateRepository
        , ILogger $logger
        , ?IToken $token = null
    ) {
        parent::__construct($l10n, $token);

        $this->userService          = $userService;
        $this->userRepository       = $userRepository;
        $this->translator           = $l10n;
        $this->permissionRepository = $permissionRepository;
        $this->loader               = $loader;
        $this->userStateRepository  = $userStateRepository;
        $this->logger               = $logger;
        $this->user                 = null;
    }


    public function onCreate(array $parameters): void {
        parent::setPermission(
            PermissionFactory::getDefaultPermission()
        );
    }

    public function create(): void {

        // a little bit out of sense, but
        // we do not want to enable registering
        // even if someone has found a hacky way
        // to enable this controller!
        $registerEnabled = $this->loader->hasApp(Application::APP_NAME_REGISTER);

        if (false === $registerEnabled) {

            $this->createAndSetResponse(
                IResponse::RESPONSE_CODE_NOT_OK
                , [
                    "message" => $this->getL10N()->translate("unknown operation")
                ]
            );

            return;

        }

        $msg = new DefaultResponse();
        $msg->setCode(Code::OK);
        $responseCode = IResponse::RESPONSE_CODE_OK;
        $message      = $this->translator->translate("User successfully registered");

        $firstName          = $this->getParameter("first_name", null);
        $lastName           = $this->getParameter("last_name", null);
        $userName           = $this->getParameter("user_name", null);
        $email              = $this->getParameter("email", null);
        $password           = $this->getParameter("password", null);
        $passwordRepeat     = $this->getParameter("password_repeat", null);
        $termsAndConditions = $this->getParameter("terms_and_conditions", null);
        $locked             = $this->getParameter("locked", "false") === "true";

        $users      = Keestash::getServer()->getUsersFromCache();
        $nameExists = false;
        $mailExists = false;

        /** @var IUser $iUser */
        foreach ($users as $iUser) {
            $mailExists = $email === $iUser->getEmail();
            $nameExists = $userName === $iUser->getName();
        }

        if (null === $firstName || "" === $firstName) {
            $responseCode = IResponse::RESPONSE_CODE_NOT_OK;
            $message      = $this->translator->translate("Please name a first name");
        }

        if (null === $lastName || "" === $lastName) {
            $responseCode = IResponse::RESPONSE_CODE_NOT_OK;
            $message      = $this->translator->translate("Please name a last name");
        }

        if (null === $userName || "" === $userName) {
            $responseCode = IResponse::RESPONSE_CODE_NOT_OK;
            $message      = $this->translator->translate("Please name a user name");
        }

        if (null === $password || "" === $password) {
            $responseCode = IResponse::RESPONSE_CODE_NOT_OK;
            $message      = $this->translator->translate("Please name a password");
        }

        if (null === $passwordRepeat || "" === $passwordRepeat) {
            $responseCode = IResponse::RESPONSE_CODE_NOT_OK;
            $message      = $this->translator->translate("Please name a password to repeat");
        }

        $stringClass = new StringClass($password);
        if (false === $stringClass->equals($passwordRepeat)) {
            $responseCode = IResponse::RESPONSE_CODE_NOT_OK;
            $message      = $this->translator->translate("Your passwords are not equal");
        }

        if (null === $termsAndConditions) {
            $responseCode = IResponse::RESPONSE_CODE_NOT_OK;
            $message      = $this->translator->translate("You have not agreed to the terms and conditions");
        }

        if (false === $this->userService->passwordHasMinimumRequirements((string) $password)) {
            $responseCode = IResponse::RESPONSE_CODE_NOT_OK;
            $message      = $this->translator->translate("Your password does not fulfill the minimum requirements");
        }

        if (true === $nameExists) {
            $responseCode = IResponse::RESPONSE_CODE_NOT_OK;
            $message      = $this->translator->translate("A user with this name already exists");
        }

        if (true === $mailExists) {
            $responseCode = IResponse::RESPONSE_CODE_NOT_OK;
            $message      = $this->translator->translate("A user with this email address already exists");
        }

        if ($responseCode === IResponse::RESPONSE_CODE_OK) {

            $user = null;
            try {
                $user = $this->userService->createUser(
                    $this->userService->toNewUser($this->getParameters())
                );

                if (true === $locked) {
                    $this->userStateRepository->lock(
                        $user
                    );
                }
            } catch (Exception $exception) {
                $this->logger->error($exception->getTraceAsString());
                $user = null;
            }

            $this->user = $user;

            if (null === $user) {
                $responseCode = IResponse::RESPONSE_CODE_NOT_OK;
                $message      = $this->translator->translate("Could not register user. Please try again");
            }

        }

        $msg->addMessage(
            $responseCode
            , [
                "response_code" => $responseCode
                , "message"     => $message
            ]
        );

        parent::setResponse(
            $msg
        );

    }

    public function afterCreate(): void {
        if (null === $this->user) return;
    }

}
