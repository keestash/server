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

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class BooleanizeMiddleware implements MiddlewareInterface {

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {
//        foreach ($request->getAttributes() as $name => $value) {
//            if ($value === 'true') {
//                $value = true;
//            }
//            if ($value === 'false') {
//                $value = false;
//            }
//            $request = $request->withAttribute(
//                $name, $value
//            );
//        }

//        $parsedBody = true === is_iterable($request->getParsedBody()) ? $request->getParsedBody() : [];
//        foreach ($parsedBody as $name => $value) {
//            if ($value === 'true') {
//                $value = true;
//            }
//            if ($value === 'false') {
//                $value = false;
//            }
//            $request = $request->withAttribute(
//                $name, $value
//            );
//        }

        return $handler->handle($request);
    }

}