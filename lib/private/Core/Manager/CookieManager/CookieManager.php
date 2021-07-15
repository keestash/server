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

namespace Keestash\Core\Manager\CookieManager;

use DateTime;
use Keestash;
use Keestash\Core\Service\HTTP\HTTPService;
use KSP\Core\ILogger\ILogger;
use KSP\Core\Manager\CookieManager\ICookieManager;
use KSP\Core\Service\Config\IConfigService;

class CookieManager implements ICookieManager {

    private HTTPService    $httpService;
    private IConfigService $configService;
    private ILogger        $logger;

    public function __construct(
        HTTPService $httpService
        , IConfigService $configService
        , ILogger $logger
    ) {
        $this->httpService   = $httpService;
        $this->configService = $configService;
        $this->logger        = $logger;

        $parsed = $this->httpService->getParsedBaseUrl(false, false);

        session_set_cookie_params(
            $this->configService->getValue('user_lifetime', 30 * 60)
            , ICookieManager::COOKIE_PATH_ENTIRE_PATH
            , $parsed['host'] ?? null
            , ICookieManager::COOKIE_SECURE
            , ICookieManager::COOKIE_HTTP_ONLY
        );
    }

    public function set(string $key, string $value, int $expireTs = 0): bool {

        return setcookie(
            $key
            , $value
            , $expireTs
            , ICookieManager::COOKIE_PATH_ENTIRE_PATH
            , $this->httpService->getBaseURL(false, false)
            , ICookieManager::COOKIE_SECURE
            , ICookieManager::COOKIE_HTTP_ONLY
        );
    }

    public function get(string $key, ?string $default = null): ?string {
        return $this->getAll()[$key] ?? $default;
    }

    public function getAll(): array {
        return $_COOKIE;
    }

    public function kill(string $key): bool {
        return $this->set($key, "", (new DateTime())->getTimestamp() - 3600);
    }

    public function killAll(): void {
        foreach ($_COOKIE as $key => $value) {
            $this->kill($key);
        }
        $_COOKIE = [];
    }

}