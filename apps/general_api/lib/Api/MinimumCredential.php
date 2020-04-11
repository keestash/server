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

use Keestash\Api\AbstractApi;
use Keestash\Api\Response\DefaultResponse;
use Keestash\Core\DTO\HTTP;
use Keestash\Core\Service\User\UserService;
use KSP\Api\IResponse;
use KSP\Core\DTO\IToken;
use KSP\Core\Repository\Permission\IPermissionRepository;
use KSP\L10N\IL10N;

class MinimumCredential extends AbstractApi {

    private $parameters           = null;
    private $userService          = null;
    private $translator           = null;
    private $permissionRepository = null;

    public function __construct(
        IL10N $l10n
        , UserService $userService
        , IPermissionRepository $permissionRepository
        , ?IToken $token = null
    ) {
        parent::__construct($l10n, $token);
        $this->userService          = $userService;
        $this->translator           = $l10n;
        $this->permissionRepository = $permissionRepository;
    }

    public function onCreate(array $parameters): void {
        $this->parameters = $parameters;
        parent::setPermission(
            $this->permissionRepository->getPermission('Public Permission')
        );
    }

    public function create(): void {
        $password = $this->parameters["password"] ?? null;
        $message  = null;

        if (null === $password) {
            $this->setResponseHelper(
                $this->translator->translate("No password provided")
                , IResponse::RESPONSE_CODE_NOT_OK
            );
            return;
        }

        $hasRequirements = $this->userService->passwordHasMinimumRequirements($password);

        if (false == $hasRequirements) {

            $this->setResponseHelper(
                $this->translator->translate("Your password does not fullfill the minimum requirements")
                , IResponse::RESPONSE_CODE_NOT_OK
            );
            return;

        }

        $this->setResponseHelper(
            $this->translator->translate("Password is valid")
            , IResponse::RESPONSE_CODE_OK
        );


    }

    private function setResponseHelper(string $message, int $responseCode) {
        $response = new DefaultResponse();
        $response->setCode(HTTP::OK);
        $response->addMessage(
            $responseCode
            , [
                "response_code" => $responseCode
                , "message"     => $message
            ]
        );

        parent::setResponse(
            $response
        );
    }

    public function afterCreate(): void {

    }

}
