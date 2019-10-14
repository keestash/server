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

use doganoo\PHPUtil\Log\FileLogger;
use Keestash\Api\Response\DefaultResponse;
use Keestash\Core\DTO\HTTP;
use KSP\Api\IApi;
use KSP\Api\IResponse;
use KSP\Core\Permission\IPermission;
use KSP\L10N\IL10N;

abstract class AbstractApi implements IApi {

    private $response           = null;
    private $permission         = null;
    private $translator         = null;
    private $associativeIndices = false;

    public function __construct(IL10N $l10n, bool $associativeIndices = false) {
        $defaultResponse = $this->createResponse(
            IResponse::RESPONSE_CODE_NOT_OK
            , [
                $l10n->translate("Could not run request")
            ]
        );

        $this->response           = $defaultResponse;
        $this->translator         = $l10n;
        $this->associativeIndices = $associativeIndices;

        if (false === $associativeIndices) {
            FileLogger::warn(static::class . " does not has associative arrays");
        }
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

    public function hasAssociativeIndices(): bool {
        return $this->associativeIndices;
    }

    public function createAndSetResponse(int $code, array $messages): void {
        $response = $this->createResponse($code, $messages);
        $this->setResponse($response);
    }

}