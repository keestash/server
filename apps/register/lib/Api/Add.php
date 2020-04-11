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

namespace KSA\Register\Api;

use doganoo\PHPUtil\Datatype\StringClass;
use doganoo\PHPUtil\Util\DateTimeUtil;
use Keestash;
use Keestash\Api\AbstractApi;
use Keestash\Api\Response\DefaultResponse;
use Keestash\Core\DTO\HTTP;
use Keestash\Core\DTO\User\User;
use Keestash\Core\Service\User\UserService;
use KSP\Api\IResponse;
use KSP\Core\DTO\IToken;
use KSP\Core\DTO\User\IUser;
use KSP\Core\Repository\Permission\IPermissionRepository;
use KSP\Core\Repository\User\IUserRepository;
use KSP\L10N\IL10N;

class Add extends AbstractApi {

    private $parameters = null;
    /** @var IUser|null $user */
    private $user              = null;
    private $userService       = null;
    private $userManager       = null;
    private $translator        = null;
    private $permissionManager = null;

    public function __construct(
        IL10N $l10n
        , UserService $userService
        , IUserRepository $userManager
        , IPermissionRepository $permissionManager
        , ?IToken $token = null
    ) {
        parent::__construct($l10n, $token);

        $this->userService       = $userService;
        $this->userManager       = $userManager;
        $this->translator        = $l10n;
        $this->permissionManager = $permissionManager;
    }


    public function onCreate(array $parameters): void {
        $this->parameters = $parameters;
        parent::setPermission(
            Keestash\Core\Permission\PermissionFactory::getDefaultPermission()
        );
    }

    public function create(): void {
        $msg = new DefaultResponse();
        $msg->setCode(HTTP::OK);
        $responseCode = IResponse::RESPONSE_CODE_OK;
        $message      = $this->translator->translate("User successfully registered");

        $firstName          = $this->parameters["first_name"] ?? null;
        $lastName           = $this->parameters["last_name"] ?? null;
        $userName           = $this->parameters["user_name"] ?? null;
        $email              = $this->parameters["email"] ?? null;
        $password           = $this->parameters["password"] ?? null;
        $passwordRepeat     = $this->parameters["password_repeat"] ?? null;
        $termsAndConditions = $this->parameters["terms_and_conditions"] ?? null;

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

        if (false === $this->userService->passwordHasMinimumRequirements($password)) {
            $responseCode = IResponse::RESPONSE_CODE_NOT_OK;
            $message      = $this->translator->translate("Your password does not fulfill the minimum requirements");
        }

        if (null !== $this->userManager->getUser($userName)) {
            $responseCode = IResponse::RESPONSE_CODE_NOT_OK;
            $message      = $this->translator->translate("A user with this name already exists");
        }

        if (null !== $this->userManager->getUserByMail($email)) {
            $responseCode = IResponse::RESPONSE_CODE_NOT_OK;
            $message      = $this->translator->translate("A user with this email address already exists");
        }

        if ($responseCode === IResponse::RESPONSE_CODE_OK) {

            Keestash::getServer()
                ->getRegistrationHookManager()
                ->executePre();

            $added = $this->addUser(
                $userName
                , $email
                , $password
                , $firstName
                , $lastName
            );

            if (false === $added) {
                $responseCode = IResponse::RESPONSE_CODE_NOT_OK;
                $message      = $this->translator->translate("Could not register user. Please try again");
            }

            Keestash::getServer()
                ->getRegistrationHookManager()
                ->executePost(
                    $added
                    , $this->user
                );

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

    private function addUser(
        string $userName
        , string $email
        , string $password
        , string $firstName
        , string $lastName
    ): bool {

        $password = $this->userService->hashPassword($password);

        $user = new User();
        $user->setName($userName);
        $user->setCreateTs(DateTimeUtil::getUnixTimestamp());
        $user->setEmail($email);
        $user->setPassword($password);
        $user->setFirstName($firstName);
        $user->setLastName($lastName);
        $user->setPhone("");
        $user->setWebsite("");
        $user->setHash($this->userService->getRandomHash());

        $userId = $this->userManager->insert($user);

        if (null === $userId) return false;

        $user->setId($userId);
        $this->user = $user;

        return true;
    }

    public function afterCreate(): void {
        if (null === $this->user) return;
    }

}
