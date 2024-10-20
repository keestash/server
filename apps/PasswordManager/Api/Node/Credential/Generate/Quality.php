<?php
declare(strict_types=1);
/**
 * Keestash
 *
 * Copyright (C) <2022> <Dogan Ucar>
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

namespace KSA\PasswordManager\Api\Node\Credential\Generate;

use Keestash\Api\Response\JsonResponse;
use Keestash\Core\DTO\Encryption\Password\Password;
use KSA\PasswordManager\Entity\IResponseCodes;
use KSP\Api\IResponse;
use KSP\Core\Service\Encryption\Password\IPasswordService;
use KSP\Core\Service\HTTP\IResponseService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;

final readonly class Quality implements RequestHandlerInterface {

    public function __construct(
        private IPasswordService   $passwordService
        , private LoggerInterface  $logger
        , private IResponseService $responseService
    ) {
    }

    #[\Override]
    public function handle(ServerRequestInterface $request): ResponseInterface {
        $value = $request->getAttribute('value');

        if (null === $value) {
            $this->logger->warning('no value given', ['requestAttributes' => $request->getAttributes()]);
            return new JsonResponse(
                [
                    "responseCode" => $this->responseService->getResponseCode(IResponseCodes::RESPONSE_NAME_NO_QUALITY_VALUE_PROVIDED)
                ]
                , IResponse::BAD_REQUEST
            );
        }

        $password = new Password();
        $password->setValue($value);
        $password = $this->addCharacterSets($value, $password);

        $password = $this->passwordService->measureQuality(
            $password
        );

        if ($password->getEntropy() === INF || $password->getEntropy() === (INF * -1)) {
            $this->logger->warning('invalid entropy', ['result' => $password]);
            $password->setValue('');
            $password->setEntropy(0);
            $password->setQuality(-1);
        }

        return new JsonResponse(
            [
                'quality' => $password
            ]
            , IResponse::OK
        );
    }

    private function addCharacterSets(string $val, Password $password): Password {
        if (true === str_contains(IPasswordService::DIGITS, $val)) {
            $password->addCharacterSet(IPasswordService::DIGITS);
        }
        if (true === str_contains(IPasswordService::SPECIAL_CHARACTERS, $val)) {
            $password->addCharacterSet(IPasswordService::SPECIAL_CHARACTERS);
        }
        if (true === str_contains(IPasswordService::UPPER_CASE_CHARACTERS, $val)) {
            $password->addCharacterSet(IPasswordService::UPPER_CASE_CHARACTERS);
        }
        if (true === str_contains(IPasswordService::LOWER_CASE_CHARACTERS, $val)) {
            $password->addCharacterSet(IPasswordService::LOWER_CASE_CHARACTERS);
        }
        return $password;
    }

}
