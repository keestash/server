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
use doganoo\DI\HTTP\IHTTPService;
use Keestash;
use Keestash\Core\Service\HTTP\HTTPService;
use Keestash\Exception\KeestashException;
use KSP\Core\ILogger\ILogger;
use KSP\Core\Manager\CookieManager\ICookieManager;
use KSP\Core\Service\Config\IConfigService;

/**
 * @deprecated
 */
class CookieManager implements ICookieManager {

    private HTTPService    $httpService;
    private IConfigService $configService;
    private ILogger        $logger;
    private IHTTPService   $libHttpService;

    public function __construct(
        HTTPService      $httpService
        , IConfigService $configService
        , ILogger        $logger
        , IHTTPService   $libHttpService
    ) {
        $this->httpService    = $httpService;
        $this->configService  = $configService;
        $this->logger         = $logger;
        $this->libHttpService = $libHttpService;

        $parsed = $this->httpService->getParsedBaseUrl(false, false);

        if (0 === count($parsed)) {
            throw new KeestashException('invalid url: ');
        }

        session_set_cookie_params(
            $this->configService->getValue('user_lifetime', 30 * 60)
            , ICookieManager::COOKIE_PATH_ENTIRE_PATH
            , (string) ($parsed['host'] ?? '')
            , ICookieManager::COOKIE_SECURE
            , ICookieManager::COOKIE_HTTP_ONLY
        );
    }

    public function set(string $key, string $value, int $expireTs = 0): bool {
        $urlParts = $this->httpService->getParsedBaseUrl(false, false);

        if (0 === count($urlParts)) {
            throw new KeestashException('invalid url');
        }

        // TODO check for https and set secure to false if http
        //   make user clear that this is insecure
        return setcookie(
            $key
            , $value
            , [
                'expires'    => $expireTs
                , 'path'     => ICookieManager::COOKIE_PATH_ENTIRE_PATH
                , 'domain'   => (string) $urlParts['host']
                , 'secure'   => ICookieManager::COOKIE_SECURE
                , 'httponly' => ICookieManager::COOKIE_HTTP_ONLY
                , 'samesite' => ICookieManager::COOKIE_SAMESITE_STRICT
            ]
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