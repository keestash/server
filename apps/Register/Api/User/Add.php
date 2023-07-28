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
use KSA\Register\Entity\Register\Event\Type;
use KSA\Register\Event\UserRegisteredEvent;
use KSP\Api\IResponse;
use KSP\Core\Repository\Payment\IPaymentLogRepository;
use KSP\Core\Service\Config\IConfigService;
use KSP\Core\Service\Event\IEventService;
use KSP\Core\Service\Payment\IPaymentService;
use KSP\Core\Service\User\Repository\IUserRepositoryService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;

class Add implements RequestHandlerInterface {

    public function __construct(
        private readonly UserService              $userService
        , private readonly LoggerInterface        $logger
        , private readonly IUserRepositoryService $userRepositoryService
        , private readonly IStringService         $stringService
        , private readonly IPaymentService        $paymentService
        , private readonly IPaymentLogRepository  $paymentLogRepository
        , private readonly Application            $application
        , private readonly IConfigService         $configService
        , private readonly IEventService          $eventService
    ) {
    }

    // TODO create a token and forward it to the frontend
    //  in order to prevent multiple user creation
    public function handle(ServerRequestInterface $request): ResponseInterface {
        $this->logger->debug('start add user');
        $isSaas             = (bool) $request->getAttribute(CoreConfigProvider::ENVIRONMENT_SAAS);
        $firstName          = $this->getParameter("first_name", $request);
        $lastName           = $this->getParameter("last_name", $request);
        $userName           = $this->getParameter("user_name", $request);
        $email              = $this->getParameter("email", $request);
        $password           = $this->getParameter("password", $request);
        $passwordRepeat     = $this->getParameter("password_repeat", $request);
        $phone              = $this->getParameter("phone", $request);
        $termsAndConditions = $this->getParameter("terms_and_conditions", $request);
        $website            = $this->getParameter("website", $request);
        // TODO fix
        $phone   = '00000000000';
        $website = $this->application->getMetaData()->get('web');

        if (true === $this->stringService->isEmpty($termsAndConditions)) {
            $this->logger->info('terms and conditions are not selected', ['termsAndConditions' => $termsAndConditions]);
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
            $this->logger->debug('start validating password');
            $this->userService->validatePasswords($password, $passwordRepeat);
        } catch (KeestashException $exception) {
            $this->logger->warning('password validation failed', ['exception' => $exception]);
            return new JsonResponse(
                [
                    "status"    => 'error'
                    , "data"    => []
                    , "message" => 'invalid passwords'
                ]
                , IResponse::BAD_REQUEST
            );
        }

        $this->logger->debug('start creating new user');
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

        $this->logger->debug('start validating new user');
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
            $this->logger->debug('start creating new user');
            $user = $this->userRepositoryService->createUser($user);
        } catch (Exception $exception) {
            $this->logger->error('error creating new user', ['exception' => $exception]);
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
            $this->logger->debug('saas mode - creating subscription');
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
            $this->logger->debug('saas mode - responding session id');
            return new JsonResponse(
                [
                    'session' => $session
                ]
                , IResponse::OK
            );
        }

        $this->eventService->execute(
            new UserRegisteredEvent(
                $user
                , true === $isSaas
                ? Type::SAAS
                : Type::REGULAR
            )
        );

        $this->logger->debug('end add user');
        return new JsonResponse(
            [
                'data' => []
            ]
            , IResponse::OK
        );
    }

    private function getParameter(string $name, ServerRequestInterface $request): string {
        $body = $request->getParsedBody();
        return (string) ($body[$name] ?? null);
    }

}
