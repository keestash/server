<?php
declare(strict_types=1);
/**
 * Keestash
 *
 * Copyright (C) <2021> <Dogan Ucar>
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

namespace KSA\Logout\Api;

use Keestash\Api\AbstractApi;
use KSP\Api\IResponse;
use KSP\Core\DTO\Token\IToken;
use KSP\Core\Repository\Token\ITokenRepository;
use KSP\L10N\IL10N;

/**
 * Class Logout
 * @package KSA\Logout\Api
 */
class Logout extends AbstractApi {

    private ITokenRepository $tokenRepository;

    public function __construct(
        IL10N $l10n
        , ITokenRepository $tokenRepository
        , ?IToken $token = null
    ) {
        parent::__construct($l10n, $token);
        $this->tokenRepository = $tokenRepository;
    }

    public function onCreate(array $parameters): void {

    }

    public function create(): void {
        $removed = $this->tokenRepository->remove($this->getToken());

        $this->createAndSetResponse(
            IResponse::RESPONSE_CODE_OK
            , [
                "logged_out" => $removed
            ]
        );
    }

    public function afterCreate(): void {

    }

}