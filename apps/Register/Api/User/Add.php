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

use DateTimeImmutable;
use doganoo\DI\Object\String\IStringService;
use Exception;
use Keestash\Api\Response\JsonResponse;
use Keestash\ConfigProvider as CoreConfigProvider;
use Keestash\Core\DTO\Payment\Log;
use Keestash\Core\Service\User\UserService;
use Keestash\Core\System\Application;
use Keestash\Exception\KeestashException;
use KSA\Register\ConfigProvider;
use KSA\Register\Event\UserRegisteredEvent;
use KSP\Api\IResponse;
use KSP\Core\Repository\Payment\IPaymentLogRepository;
use KSP\Core\Service\App\ILoaderService;
use KSP\Core\Service\Config\IConfigService;
use KSP\Core\Service\Event\IEventService;
use KSP\Core\Service\Payment\IPaymentService;
use KSP\Core\Service\User\Repository\IUserRepositoryService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;

class Add implements RequestHandlerInterface {

    private UserService            $userService;
    private ILoaderService         $loader;
    private LoggerInterface        $logger;
    private IUserRepositoryService $userRepositoryService;
    private IStringService         $stringService;
    private IPaymentService        $paymentService;
    private IPaymentLogRepository  $paymentLogRepository;
    private Application            $application;
    private IConfigService         $configService;

    public function __construct(
        UserService                      $userService
        , ILoaderService                 $loader
        , LoggerInterface                $logger
        , IUserRepositoryService         $userRepositoryService
        , IStringService                 $stringService
        , IPaymentService                $paymentService
        , IPaymentLogRepository          $paymentLogRepository
        , Application                    $application
        , IConfigService                 $configService
        , private readonly IEventService $eventService
    ) {

        $this->userService           = $userService;
        $this->loader                = $loader;
        $this->logger                = $logger;
        $this->userRepositoryService = $userRepositoryService;
        $this->stringService         = $stringService;
        $this->paymentService        = $paymentService;
        $this->paymentLogRepository  = $paymentLogRepository;
        $this->application           = $application;
        $this->configService         = $configService;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface {
        // a little bit out of sense, but
        // we do not want to enable registering
        // even if someone has found a hacky way
        // to enable this controller!
        $registerEnabled = $this->loader->hasApp(ConfigProvider::APP_ID);
        $isSaas          = $request->getAttribute(CoreConfigProvider::ENVIRONMENT_SAAS);

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
        $passwordRepeat     = $this->getParameter("password_repeat", $request);
        $phone              = $this->getParameter("phone", $request);
        $termsAndConditions = $this->getParameter("terms_and_conditions", $request);
        $website            = $this->getParameter("website", $request);

        if (true === $isSaas) {
            $phone   = '00000000000';
            $website = $this->application->getMetaData()->get('web');
        }

        if (true === $this->stringService->isEmpty($termsAndConditions)) {
            return new JsonResponse(
                [
                    "status"    => 'error'
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
                , 'locked'     => true
            ]
        );

        $result = $this->userService->validateNewUser($user);
        if ($result->length() > 0) {

            $this->logger->error('error validating new user', ['exception' => $result->toArray()]);

            return new JsonResponse(
                [
                    "status"    => 'error'
                    , "message" => 'invalid new user'
                    , 'data'    => $result->toArray()
                ]

                , IResponse::BAD_REQUEST
            );
        }

        try {
            $user = $this->userRepositoryService->createUser($user);
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

        if (true === $isSaas) {
            $session = $this->paymentService->createSubscription(
                (string) $this->configService->getValue('stripe_price_id')
            );
            $log     = new Log();
            $log->setKey($session->id);
            $log->setLog([
                'session' => $session->toArray(),
                'user'    => $user
            ]);
            $log->setCreateTs(new DateTimeImmutable());
            $this->paymentLogRepository->insert($log);
            return new JsonResponse(
                [
                    'session' => $session
                ]
                , IResponse::OK
            );
        }

        $this->eventService->execute(
            new UserRegisteredEvent($user)
        );

        return new JsonResponse(
            []
            , IResponse::OK
        );
    }

    private function getParameter(string $name, ServerRequestInterface $request): string {
        $body = $request->getParsedBody();
        return (string) ($body[$name] ?? null);
    }

}
