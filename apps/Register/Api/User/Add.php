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
use Exception;
use Keestash\Api\Response\JsonResponse;
use Keestash\Core\Service\User\UserService;
use KSA\Register\ConfigProvider;
use KSP\Api\IResponse;
use KSP\App\ILoader;
use KSP\Core\DTO\User\IUser;
use KSP\Core\ILogger\ILogger;
use KSP\Core\Repository\User\IUserRepository;
use KSP\Core\Service\User\Repository\IUserRepositoryService;
use KSP\L10N\IL10N;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class Add implements RequestHandlerInterface {

    private UserService            $userService;
    private IUserRepository        $userRepository;
    private IL10N                  $translator;
    private ILoader                $loader;
    private ILogger                $logger;
    private IUserRepositoryService $userRepositoryService;

    public function __construct(
        IL10N                    $l10n
        , UserService            $userService
        , IUserRepository        $userRepository
        , ILoader                $loader
        , ILogger                $logger
        , IUserRepositoryService $userRepositoryService
    ) {

        $this->userService           = $userService;
        $this->userRepository        = $userRepository;
        $this->translator            = $l10n;
        $this->loader                = $loader;
        $this->logger                = $logger;
        $this->userRepositoryService = $userRepositoryService;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface {

        // a little bit out of sense, but
        // we do not want to enable registering
        // even if someone has found a hacky way
        // to enable this controller!
        $registerEnabled = $this->loader->hasApp(ConfigProvider::APP_ID);

        if (false === $registerEnabled) {

            return new JsonResponse(
                [
                    "message" => $this->translator->translate("unknown operation")
                ]
                , IResponse::BAD_REQUEST
            );

        }

        $responseCode = IResponse::RESPONSE_CODE_OK;
        $message      = $this->translator->translate("User successfully registered");

        $firstName          = $this->getParameter("first_name", $request);
        $lastName           = $this->getParameter("last_name", $request);
        $userName           = $this->getParameter("user_name", $request);
        $email              = $this->getParameter("email", $request);
        $password           = $this->getParameter("password", $request);
        $passwordRepeat     = $this->getParameter("password_repeat", $request);
        $termsAndConditions = $this->getParameter("terms_and_conditions", $request);
        $locked             = $this->getParameter("locked", $request) === "true";

        $users      = $this->userRepository->getAll();
        $nameExists = false;
        $mailExists = false;

        /** @var IUser $iUser */
        foreach ($users as $iUser) {
            $mailExists = $email === $iUser->getEmail();
            $nameExists = $userName === $iUser->getName();
        }

        if ("" === $firstName) {
            $responseCode = IResponse::RESPONSE_CODE_NOT_OK;
            $message      = $this->translator->translate("Please name a first name");
        }

        if ("" === $lastName) {
            $responseCode = IResponse::RESPONSE_CODE_NOT_OK;
            $message      = $this->translator->translate("Please name a last name");
        }

        if ("" === $userName) {
            $responseCode = IResponse::RESPONSE_CODE_NOT_OK;
            $message      = $this->translator->translate("Please name a user name");
        }

        if ("" === $password) {
            $responseCode = IResponse::RESPONSE_CODE_NOT_OK;
            $message      = $this->translator->translate("Please name a password");
        }

        if ("" === $passwordRepeat) {
            $responseCode = IResponse::RESPONSE_CODE_NOT_OK;
            $message      = $this->translator->translate("Please name a password to repeat");
        }

        $stringClass = new StringClass($password);
        if (false === $stringClass->equals($passwordRepeat)) {
            $responseCode = IResponse::RESPONSE_CODE_NOT_OK;
            $message      = $this->translator->translate("Your passwords are not equal");
        }

        if ("" === $termsAndConditions) {
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

            try {
                $user = $this->userRepositoryService->createUser(
                    $this->userService->toNewUser((array) $request->getParsedBody())
                );

            } catch (Exception $exception) {
                $this->logger->error($exception->getTraceAsString());
                $user = null;
            }

            if (null === $user) {
                $responseCode = IResponse::RESPONSE_CODE_NOT_OK;
                $message      = $this->translator->translate("Could not register user. Please try again");
            }

        }

        return new JsonResponse(
            [
                "response_code" => $responseCode
                , "message"     => $message
            ]
            ,
            $responseCode === IResponse::RESPONSE_CODE_OK
                ? IResponse::OK
                : IResponse::INTERNAL_SERVER_ERROR
        );

    }

    private function getParameter(string $name, ServerRequestInterface $request): string {
        $body = $request->getParsedBody();
        return (string) ($body[$name] ?? null);
    }

}
