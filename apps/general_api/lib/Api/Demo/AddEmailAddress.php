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

namespace KSA\GeneralApi\Api\Demo;

use Keestash\Api\AbstractApi;
use Keestash\Core\Manager\CookieManager\CookieManager;
use Keestash\Core\Service\User\UserService;
use KSA\GeneralApi\Exception\GeneralApiException;
use KSA\GeneralApi\Repository\DemoUsersRepository;
use KSP\Api\IResponse;
use KSP\Core\DTO\Token\IToken;
use KSP\L10N\IL10N;

class AddEmailAddress extends AbstractApi {

    private DemoUsersRepository $demoUsersRepository;
    private UserService         $userService;
    private CookieManager       $cookieManager;

    public function __construct(
        IL10N $l10n
        , DemoUsersRepository $demoUsersRepository
        , UserService $userService
        , CookieManager $cookieManager
        , ?IToken $token = null
    ) {
        parent::__construct($l10n, $token);

        $this->demoUsersRepository = $demoUsersRepository;
        $this->userService         = $userService;
        $this->cookieManager       = $cookieManager;
    }

    public function onCreate(array $parameters): void {

    }

    public function create(): void {
        $email = $this->getParameter("email");

        if (false === $this->userService->validEmail($email)) {
            throw new GeneralApiException('invalid email');
        }

        $this->demoUsersRepository->add($email);
        $put = $this->cookieManager->set("demo-submitted", "true");

        $this->createAndSetResponse(
            IResponse::RESPONSE_CODE_OK
            , [
                "message" => "ok"
                , "cookie" => $put
            ]
        );
    }

    public function afterCreate(): void {

    }

}