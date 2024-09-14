<?php
declare(strict_types=1);
/**
 * Keestash
 *
 * Copyright (C) <2023> <Dogan Ucar>
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

namespace Keestash\Core\Service\HTTP;

use Keestash\ConfigProvider;
use Keestash\Exception\KeestashException;
use KSP\Core\Service\HTTP\IResponseService;
use Laminas\Config\Config;

class ResponseService implements IResponseService {

    public function __construct(
        private readonly Config $config
    ) {
    }

    #[\Override]
    public function getResponseCode(string $name): int {
        /** @var null|Config $responseCodes */
        $responseCodes = $this->config->get(ConfigProvider::RESPONSE_CODES);
        if (null === $responseCodes) {
            throw new KeestashException();
        }
        /** @var null|int $responseCode */
        $responseCode = $responseCodes->get($name);
        if (null === $responseCode) {
            throw new KeestashException();
        }
        return (int) $responseCode;
    }

}