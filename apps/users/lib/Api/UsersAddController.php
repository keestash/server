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

namespace KSA\Users\Api;

use doganoo\PHPUtil\Datatype\StringClass;
use doganoo\PHPUtil\Util\DateTimeUtil;
use Keestash\Api\AbstractApi;
use Keestash\Api\Response\DefaultResponse;
use Keestash\Core\DTO\HTTP;
use Keestash\Core\DTO\User;
use Keestash\Core\Service\UserService;
use KSP\Api\IResponse;
use KSP\Core\DTO\IToken;
use KSP\Core\DTO\IUser;
use KSP\Core\Repository\User\IUserRepository;
use KSP\L10N\IL10N;

class UsersAddController extends AbstractApi {

    private $passwordRepeat = null;
    /** @var IUser $user */
    private $user = null;

    private $userService = null;
    private $userManager = null;

    public function __construct(
        IL10N $l10n
        , UserService $userService
        , IUserRepository $userManager
        , ?IToken $token = null
    ) {
        parent::__construct($l10n, $token);

        $this->userService = $userService;
        $this->userManager = $userManager;
    }

    public function onCreate(array $parameters): void {
        $this->passwordRepeat = $parameters["password_repeat"];
        $this->user           = $this->toUser($params);
    }

    private function toUser($params): IUser {
        $userName  = $params[0];
        $firstName = $params[1];
        $lastName  = $params[2];
        $email     = $params[3];
        $phone     = $params[4];
        $password  = $params[5];
        $website   = $params[7];

        $user = new User();
        $user->setName($userName);
        $user->setFirstName($firstName);
        $user->setLastName($lastName);
        $user->setEmail($email);
        $user->setPhone($phone);
        $user->setPassword($password);
        $user->setCreateTs(DateTimeUtil::getUnixTimestamp());
        $user->setWebsite($website);
        $user->setHash($this->userService->getRandomHash());

        return $user;
    }

    public function create(): void {
        if (null === $this->user) return;
        if (true === $this->userManager->nameExists($this->user->getName())) return;
        if (false === (new StringClass($this->user->getPassword()))->equals($this->passwordRepeat)) return;
        if (false === $this->userService->passwordHasMinimumRequirements($this->user->getPassword())) return;
        if (false === $this->userService->validEmail($this->user->getEmail())) return;
        if (false === $this->userService->validWebsite($this->user->getWebsite())) return;

        $hash = $this->userService->hashPassword($this->user->getPassword());
        $this->user->setPassword($hash);

        $userId = $this->userManager->insert($this->user);

        if (null !== $userId) {

            $response = new DefaultResponse();
            $response->setCode(HTTP::OK);
            $response->addMessage(IResponse::RESPONSE_CODE_OK,
                [
                    "User Created"
                ]
            );

            parent::setResponse($response);
        }
    }

    public function afterCreate(): void {

    }

}