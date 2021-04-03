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

use doganoo\PHPUtil\HTTP\Code;
use Keestash;
use Keestash\Api\Response\DefaultResponse;
use Keestash\Core\Service\HTTP\Input\SanitizerService as InputSanitizer;
use Keestash\Core\Service\HTTP\Output\SanitizerService as OutputSanitizer;
use KSP\Api\IApi;
use KSP\Api\IResponse;
use KSP\Core\DTO\File\Upload\IFileList;
use KSP\Core\DTO\Token\IToken;
use KSP\L10N\IL10N;

abstract class AbstractApi implements IApi {

    private IResponse       $response;
    private IL10N           $translator;
    private array           $parameters;
    private ?IToken         $token;
    private InputSanitizer  $inputSanitizer;
    private OutputSanitizer $outputSanitizer;
    private bool            $parametersSanitized = false;
    private IFileList       $files;

    public function __construct(
        IL10N $l10n
        , ?IToken $token = null
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

    protected function createResponse(int $code, array $messages): IResponse {
        $defaultResponse = new DefaultResponse();
        $defaultResponse->setCode(Code::OK);
        $defaultResponse->addMessage(
            $code
            , [
                "code"       => $code
                , "messages" => $this->outputSanitizer->sanitizeAll($messages)
            ]
        );

        return $defaultResponse;
    }

    public function getResponse(): IResponse {
        return $this->response;
    }

    protected function setResponse(IResponse $response): void {
        $this->response = $response;
    }

    public function createAndSetResponse(int $code, array $messages): void {
        $response = $this->createResponse($code, $messages);
        $this->setResponse($response);
    }

    public function getFiles(): IFileList {
        return $this->files;
    }

    public function setFiles(IFileList $files): void {
        $this->files = $files;
    }

    protected function getParameter(string $name, ?string $default = null): ?string {
        return $this->getParameters()[$name] ?? $default;
    }

    protected function getParameters(): array {
        if (false === $this->parametersSanitized) {
            $this->parameters          = $this->inputSanitizer->sanitizeAll($this->parameters);
            $this->parametersSanitized = true;
        }
        return $this->parameters;
    }

    public function setParameters(array $parameters): void {
        $this->parameters          = $parameters;
        $this->parametersSanitized = false;
    }

    protected function getL10N(): IL10N {
        return $this->translator;
    }

    protected function getToken(): ?IToken {
        return $this->token;
    }

}
