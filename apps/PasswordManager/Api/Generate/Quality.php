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

namespace KSA\PasswordManager\Api\Generate;

use Keestash\Api\Response\JsonResponse;
use Keestash\Core\DTO\Encryption\Password\Password;
use KSP\Api\IResponse;
use KSP\Core\Service\Encryption\Password\IPasswordService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class Quality implements RequestHandlerInterface {

    private IPasswordService $passwordService;

    public function __construct(IPasswordService $passwordService) {
        $this->passwordService = $passwordService;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface {
        $value = $request->getAttribute('value');

        if (null === $value) {
            return new JsonResponse([], IResponse::BAD_REQUEST);
        }

        $password = new Password();
        $password->setValue($value);
        $password = $this->addCharacterSets($value, $password);

        $password = $this->passwordService->measureQuality(
            $password
        );

        if ($password->getEntropy() === INF || $password->getEntropy() === (INF * -1)) {
            $password->setValue('');
            $password->setEntropy(0);
            $password->setQuality(-1);
        }

        return new JsonResponse(
            [
                'quality' => $password->getQuality()
            ]
            , IResponse::OK
        );
    }

    private function addCharacterSets(string $val, Password $password): Password {
        if (false !== strpos(IPasswordService::DIGITS, $val)) {
            $password->addCharacterSet(IPasswordService::DIGITS);
        }
        if (false !== strpos(IPasswordService::SPECIAL_CHARACTERS, $val)) {
            $password->addCharacterSet(IPasswordService::SPECIAL_CHARACTERS);
        }
        if (false !== strpos(IPasswordService::UPPER_CASE_CHARACTERS, $val)) {
            $password->addCharacterSet(IPasswordService::UPPER_CASE_CHARACTERS);
        }
        if (false !== strpos(IPasswordService::LOWER_CASE_CHARACTERS, $val)) {
            $password->addCharacterSet(IPasswordService::LOWER_CASE_CHARACTERS);
        }
        return $password;
    }

}