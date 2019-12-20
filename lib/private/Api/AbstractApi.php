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

namespace Keestash\Api;

use Keestash;
use Keestash\Api\Response\DefaultResponse;
use Keestash\Core\DTO\HTTP;
use KSP\Api\IApi;
use KSP\Api\IResponse;
use KSP\Core\DTO\IToken;
use KSP\Core\Permission\IPermission;
use KSP\L10N\IL10N;

abstract class AbstractApi implements IApi {

    private $response   = null;
    private $permission = null;
    private $translator = null;
    private $parameters = null;
    private $token      = null;

    public function __construct(
        IL10N $l10n
        , ?IToken $token = null
    ) {
        $defaultResponse = $this->createResponse(
            IResponse::RESPONSE_CODE_NOT_OK
            , [
                $l10n->translate("Could not run request")
            ]
        );

        $this->response   = $defaultResponse;
        $this->translator = $l10n;
        $this->parameters = [];
        $this->token      = $token;
    }

    public function getResponse(): IResponse {
        return $this->response;
    }

    protected function setResponse(IResponse $response): void {
        $this->response = $response;
    }

    public function getPermission(): IPermission {
        return $this->permission;
    }

    public function setPermission(IPermission $permission): void {
        $this->permission = $permission;
    }

    protected function getL10N(): IL10N {
        return $this->translator;
    }

    protected function setParameters(array $parameters): void {
        $this->parameters = $parameters;
    }

    protected function getParameters(): array {
        return $this->parameters;
    }

    protected function getToken(): ?IToken {
        return $this->token;
    }

    protected function createResponse(int $code, array $messages): IResponse {
        $defaultResponse = new DefaultResponse();
        $defaultResponse->setCode(HTTP::OK);
        $defaultResponse->addMessage(
            $code
            , [
                "code"       => $code
                , "messages" => $messages
            ]
        );

        return $defaultResponse;
    }

    public function createAndSetResponse(int $code, array $messages): void {
        $response = $this->createResponse($code, $messages);
        $this->setResponse($response);
    }

}