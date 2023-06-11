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

namespace KSA\PasswordManager\Test\Service;

use GuzzleHttp\Psr7\Utils;
use Keestash\Core\DTO\Token\Token;
use KSP\Core\DTO\Token\IToken;
use KSP\Core\DTO\User\IUser;
use Laminas\Diactoros\ServerRequest;
use Laminas\Diactoros\ServerRequestFactory;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ServerRequestInterface;

class RequestService {

    public function getVirtualRequestWithToken(
        IUser   $user
        , array $server = []
        , array $query = []
        , array $body = []
        , array $cookies = []
        , array $files = []
    ): ServerRequestInterface {
        $request = ServerRequestFactory::fromGlobals(
            []
            , []
            , $body
            , []
            , []
        );
        $token   = new Token();
        $token->setUser($user);
        $token->setCreateTs(new \DateTime());
        $token->setName("Test.Keestash.PasswordManager");
        $token->setValue(bin2hex(random_bytes(16)));
        $token->setId(1);
        return $request->withAttribute(IToken::class, $token);
    }

    public function getRequestWithToken(
        IUser    $user
        , string $method
        , string $uri
        , array  $body = []
        , array  $headers = []
        , array  $files = []
        , array  $server = []
        , array  $query = []
        , array  $cookies = []
    ): ServerRequestInterface {
        $request = $this->getRequest($method, $uri, $body, $headers, $files, $server, $query, $cookies);
        $token   = new Token();
        $token->setUser($user);
        $token->setCreateTs(new \DateTime());
        $token->setName("Test.Keestash.PasswordManager");
        $token->setValue(bin2hex(random_bytes(16)));
        $token->setId(1);
        return $request->withAttribute(IToken::class, $token);
    }

    public function getRequest(
        string   $method
        , string $uri
        , array  $body = []
        , array  $headers = []
        , array  $files = []
        , array  $server = []
        , array  $query = []
        , array  $cookies = []
    ): ServerRequestInterface {
        return new ServerRequest(
            $server
            , $files
            , $uri
            , $method
            , Utils::streamFor(json_encode($body, JSON_THROW_ON_ERROR))
            , array_merge(
                ['Content-Type' => 'application/json']
                , $headers
            )
            , $cookies
            , $query
        );
    }

}