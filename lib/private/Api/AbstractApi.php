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
use Keestash\Core\Service\HTTP\Input\SanitizerService as InputSanitizer;
use Keestash\Core\Service\HTTP\Output\SanitizerService as OutputSanitizer;
use KSP\Api\IApi;
use KSP\Api\IResponse;
use KSP\Core\DTO\IJsonToken;
use KSP\Core\Permission\IPermission;
use KSP\L10N\IL10N;

abstract class AbstractApi implements IApi {

    private $response   = null;
    private $permission = null;
    private $translator = null;
    private $parameters = null;
    private $token      = null;
    /** @var InputSanitizer $inputSanitizer */
    private $inputSanitizer = null;
    /** @var OutputSanitizer $outputSanitizer */
    private $outputSanitizer     = null;
    private $parametersSanitized = false;

    public function __construct(
        IL10N $l10n
        , ?IJsonToken $token = null
    ) {

        $this->translator = $l10n;
        $this->token      = $token;
        $this->setParameters([]);
        // TODO inject via constructor once you are ready to adapt all extending classes
        $this->inputSanitizer = Keestash::getServer()->query(InputSanitizer::class);
        // TODO inject via constructor once you are ready to adapt all extending classes
        $this->outputSanitizer = Keestash::getServer()->query(OutputSanitizer::class);

        $this->response = $this->createResponse(
            IResponse::RESPONSE_CODE_NOT_OK
            , [
                $l10n->translate("Could not run request")
            ]
        );

    }

    public function setParameters(array $parameters): void {
        $this->parameters          = $parameters;
        $this->parametersSanitized = false;
    }

    protected function getParameters(): array {
        if (false === $this->parametersSanitized) {
            $this->parameters          = $this->inputSanitizer->sanitizeAll($this->parameters);
            $this->parametersSanitized = true;
        }
        return $this->parameters;
    }

    protected function getParameter(string $name, ?string $default = null): ?string {
        return $this->getParameters()[$name] ?? $default;
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

    protected function getToken(): ?IJsonToken {
        return $this->token;
    }

    protected function createResponse(int $code, array $messages): IResponse {
        $defaultResponse = new DefaultResponse();
        $defaultResponse->setCode(HTTP::OK);
        $defaultResponse->addMessage(
            $code
            , [
                "code"       => $code
                , "messages" => $this->outputSanitizer->sanitizeAll($messages)
            ]
        );

        return $defaultResponse;
    }

    public function createAndSetResponse(int $code, array $messages): void {
        $response = $this->createResponse($code, $messages);
        $this->setResponse($response);
    }

}
