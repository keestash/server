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

namespace Keestash\Api\Response;

use KSP\Api\IResponse;
use Laminas\Diactoros\Response\TextResponse;

class ImageResponse extends TextResponse {

    public function __construct($path, string $mimeType) {

        $content    = null;
        $statusCode = 404;
        if (is_file($path)) {
            $content    = file_get_contents($path);
            $statusCode = 200;
        }
        parent::__construct(
            base64_encode((string) $content)
            , $statusCode
            , [
                IResponse::HEADER_CONTENT_TYPE => $mimeType
            ]
        );
    }

}