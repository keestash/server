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

namespace KSA\GeneralApi\Api;

use Keestash\Api\Response\LegacyResponse;
use Keestash\Core\Service\User\UserService;
use KSP\Api\IResponse;
use KSP\L10N\IL10N;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class MinimumCredential implements RequestHandlerInterface {

    private UserService $userService;
    private IL10N       $translator;

    public function __construct(
        IL10N $l10n
        , UserService $userService
    ) {
        $this->userService = $userService;
        $this->translator  = $l10n;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface {
        $password = $request->getQueryParams()["password"] ?? null;
        $message  = null;

        if (null === $password) {
            return $this->setResponseHelper(
                $this->translator->translate("No password provided")
                , IResponse::RESPONSE_CODE_NOT_OK
            );
        }

        $hasRequirements = $this->userService->passwordHasMinimumRequirements($password);

        if (false == $hasRequirements) {

            return $this->setResponseHelper(
                $this->translator->translate("Your password does not fulfill the minimum requirements")
                , IResponse::RESPONSE_CODE_NOT_OK
            );

        }

        return $this->setResponseHelper(
            $this->translator->translate("Password is valid")
            , IResponse::RESPONSE_CODE_OK
        );

    }

    private function setResponseHelper(string $message, int $responseCode): ResponseInterface {
        return LegacyResponse::fromData(
            $responseCode
            , [
                "response_code" => $responseCode
                , "message"     => $message
            ]
        );
    }

}
