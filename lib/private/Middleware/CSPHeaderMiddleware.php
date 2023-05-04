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

namespace Keestash\Middleware;

use KSP\Api\IResponse;
use KSP\Core\Service\Config\IConfigService;
use KSP\Core\Service\HTTP\IHTTPService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class CSPHeaderMiddleware implements MiddlewareInterface {

    public const DIRECTIVE_NAME_DEFAULT_SRC               = 'default-src';
    public const DIRECTIVE_NAME_SCRIPT_SRC                = 'script-src';
    public const DIRECTIVE_NAME_STYLE_SRC                 = 'style-src';
    public const DIRECTIVE_NAME_IMG_SRC                   = 'img-src';
    public const DIRECTIVE_NAME_CONNECT_SRC               = 'connect-src';
    public const DIRECTIVE_NAME_FONT_SRC                  = 'font-src';
    public const DIRECTIVE_NAME_OBJECT_SRC                = 'object-src';
    public const DIRECTIVE_NAME_MEDIA_SRC                 = 'media-src';
    public const DIRECTIVE_NAME_FRAME_SRC                 = 'frame-src';
    public const DIRECTIVE_NAME_FORM_ACTION               = 'form-action';
    public const DIRECTIVE_NAME_WORKER_SRC                = 'worker-src';
    public const DIRECTIVE_NAME_MANIFEST_SRC              = 'manifest-src';
    public const DIRECTIVE_NAME_PREFETCH_SRC              = 'prefetch-src';
    public const DIRECTIVE_NAME_REQUIRE_TRUSTED_TYPES_FOR = 'require-trusted-types-for';

    public const CSP_FIELD_DIRECTIVES = [
        CSPHeaderMiddleware::DIRECTIVE_NAME_DEFAULT_SRC,
        CSPHeaderMiddleware::DIRECTIVE_NAME_STYLE_SRC,
        CSPHeaderMiddleware::DIRECTIVE_NAME_SCRIPT_SRC,
        CSPHeaderMiddleware::DIRECTIVE_NAME_IMG_SRC,
        CSPHeaderMiddleware::DIRECTIVE_NAME_CONNECT_SRC,
        CSPHeaderMiddleware::DIRECTIVE_NAME_FONT_SRC,
        CSPHeaderMiddleware::DIRECTIVE_NAME_MEDIA_SRC,
        CSPHeaderMiddleware::DIRECTIVE_NAME_FRAME_SRC,
        CSPHeaderMiddleware::DIRECTIVE_NAME_FORM_ACTION,
        CSPHeaderMiddleware::DIRECTIVE_NAME_WORKER_SRC,
        CSPHeaderMiddleware::DIRECTIVE_NAME_MANIFEST_SRC,
        CSPHeaderMiddleware::DIRECTIVE_NAME_PREFETCH_SRC,
    ];

    public function __construct(
        private readonly IConfigService $configService
        , private readonly IHTTPService $httpService
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {
        $backendUrl  = $this->httpService->getBaseURL(false);
        $frontendUrl = $this->configService->getValue('frontend_url');
        $value       = '';

        foreach (CSPHeaderMiddleware::CSP_FIELD_DIRECTIVES as $name) {
            $value = $value . $this->generateValue(
                    $name
                    , [
                        $backendUrl,
                        $frontendUrl
                    ]
                );
        }

        $value = $value . $this->generateValue(
                CSPHeaderMiddleware::DIRECTIVE_NAME_OBJECT_SRC
                , ["'none'"]
            );
        $value = $value . $this->generateValue(
                CSPHeaderMiddleware::DIRECTIVE_NAME_REQUIRE_TRUSTED_TYPES_FOR
                , ["'script'"]
            );

        return $handler->handle($request)->withHeader(
            IResponse::HEADER_CONTENT_SECURITY_POLICY
            , $value
        );
    }

    private function generateValue(string $name, array $values): string {
        $values = array_filter(
            $values,
            static function ($val): bool {
                return is_string($val);
            }
        );

        $result = implode(" ", $values) . ';';
        return "$name $result";
    }

}
