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

namespace KSA\Register\Api\User;

use doganoo\DI\Object\String\IStringService;
use Exception;
use Keestash\Api\Response\JsonResponse;
use Keestash\Core\Service\User\UserService;
use Keestash\Exception\KeestashException;
use KSA\Register\ConfigProvider;
use KSP\Api\IResponse;
use KSP\App\ILoader;
use KSP\Core\ILogger\ILogger;
use KSP\Core\Service\User\Repository\IUserRepositoryService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class Add implements RequestHandlerInterface {

    private UserService            $userService;
    private ILoader                $loader;
    private ILogger                $logger;
    private IUserRepositoryService $userRepositoryService;
    private IStringService         $stringService;

    public function __construct(
        UserService              $userService
        , ILoader                $loader
        , ILogger                $logger
        , IUserRepositoryService $userRepositoryService
        , IStringService         $stringService
    ) {

        $this->userService           = $userService;
        $this->loader                = $loader;
        $this->logger                = $logger;
        $this->userRepositoryService = $userRepositoryService;
        $this->stringService         = $stringService;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface {
        // a little bit out of sense, but
        // we do not want to enable registering
        // even if someone has found a hacky way
        // to enable this controller!
        $registerEnabled = $this->loader->hasApp(ConfigProvider::APP_ID);

        if (false === $registerEnabled) {

            return new JsonResponse(
                ['unknown operation']
                , IResponse::BAD_REQUEST
            );

        }

        // TODO create a token and forward it to the frontend
        //  in order to prevent multiple user creation
        $firstName          = $this->getParameter("first_name", $request);
        $lastName           = $this->getParameter("last_name", $request);
        $userName           = $this->getParameter("user_name", $request);
        $email              = $this->getParameter("email", $request);
        $password           = $this->getParameter("password", $request);
        $passwordRepeat     = $password;
        $phone              = $this->getParameter("phone", $request);
        $termsAndConditions = $this->getParameter("terms_and_conditions", $request);
        $website            = $this->getParameter("website", $request);

        if (true === $this->stringService->isEmpty($termsAndConditions)) {
            return new JsonResponse(
                [
                    "status"    => 'error'
                    , "data"    => []
                    , "message" => 'terms and conditions are not checked'
                ]
                , IResponse::BAD_REQUEST
            );
        }

        try {
            $this->userService->validatePasswords($password, $passwordRepeat);
        } catch (KeestashException $exception) {
            return new JsonResponse(
                [
                    "status"    => 'error'
                    , "data"    => []
                    , "message" => 'invalid passwords'
                ]
                , IResponse::BAD_REQUEST
            );
        }

        $user = $this->userService->toNewUser(
            [
                'user_name'    => $userName
                , 'email'      => $email
                , 'last_name'  => $lastName
                , 'first_name' => $firstName
                , 'password'   => $password
                , 'phone'      => $phone
                , 'website'    => $website
            ]
        );

        try {
            $this->userService->validateNewUser($user);
        } catch (KeestashException $exception) {
            $this->logger->error('error validating new user', ['exception' => $exception]);
            return new JsonResponse(
                [
                    "status"    => 'error'
                    , "data"    => []
                    , "message" => 'invalid new user'
                ]

                , IResponse::BAD_REQUEST
            );
        }

        try {
            $this->userRepositoryService->createUser($user);
        } catch (Exception $exception) {
            $this->logger->error($exception->getTraceAsString());
            return new JsonResponse(
                [
                    "status"    => 'error'
                    , "data"    => []
                    , "message" => 'could not create user'
                ]
                , IResponse::INTERNAL_SERVER_ERROR
            );
        }

        return new JsonResponse([], IResponse::OK);
    }

    private function getParameter(string $name, ServerRequestInterface $request): string {
        $body = $request->getParsedBody();
        return (string) ($body[$name] ?? null);
    }

}
