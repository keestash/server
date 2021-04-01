<?php
declare(strict_types=1);
/**
 * server
 *
 * Copyright (C) <2020> <Dogan Ucar>
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

namespace KSA\Users\Api\User;

use Keestash;
use Keestash\Api\AbstractApi;
use KSP\Api\IResponse;
use KSP\Core\DTO\Token\IToken;
use KSP\Core\ILogger\ILogger;
use KSP\L10N\IL10N;

class GetAll extends AbstractApi {

    private ILogger $logger;

    public function __construct(
        IL10N $l10n
        , ILogger $logger
        , ?IToken $token = null
    ) {
        parent::__construct($l10n, $token);

        $this->logger = $logger;
    }

    public function onCreate(array $parameters): void {

    }

    public function create(): void {
        $this->createAndSetResponse(
            IResponse::RESPONSE_CODE_OK
            , [
                "users" => Keestash::getServer()->getUsersFromCache()
            ]
        );
    }

    public function afterCreate(): void {

    }

}
