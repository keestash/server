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
use doganoo\DI\Object\String\StringServiceInterface;
use Exception;
use OpenApi\Attributes as OA;
use Keestash\Api\Response\JsonResponse;
use Keestash\ConfigProvider as CoreConfigProvider;
use Keestash\Core\DTO\Payment\Log;
use Keestash\Core\Service\User\UserService;
use Keestash\Core\System\Application;
use Keestash\Exception\Payment\PaymentException;
use KSA\Register\Entity\IResponseCodes;
use KSA\Register\Entity\Register\Event\Type;
use KSA\Register\Event\UserRegisteredEvent;
use KSP\Api\IResponse;
use KSP\Core\Repository\Payment\IPaymentLogRepository;
use KSP\Core\Service\Config\IConfigService;
use KSP\Core\Service\Event\IEventService;
use KSP\Core\Service\HTTP\IResponseService;
use KSP\Core\Service\Metric\ICollectorService;
use KSP\Core\Service\Payment\IPaymentService;
use KSP\Core\Service\User\Repository\IUserRepositoryService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;

#[OA\Post(
    path: '/register/add',
    operationId: 'registerAdd',
    summary: 'Register a new user account',
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['first_name', 'last_name', 'user_name', 'email', 'password', 'password_repeat'],
            properties: [
                new OA\Property(property: 'first_name', type: 'string'),
                new OA\Property(property: 'last_name', type: 'string'),
                new OA\Property(property: 'user_name', type: 'string'),
                new OA\Property(property: 'email', type: 'string', format: 'email'),
                new OA\Property(property: 'password', type: 'string', format: 'password'),
                new OA\Property(property: 'password_repeat', type: 'string', format: 'password'),
                new OA\Property(property: 'phone', type: 'string'),
                new OA\Property(property: 'terms_and_conditions', type: 'string'),
                new OA\Property(property: 'website', type: 'string', format: 'uri'),
                new OA\Property(property: 'key', type: 'string'),
                new OA\Property(property: 'kdf_version', type: 'string'),
            ]
        )
    ),
    tags: ['Registration'],
    responses: [
        new OA\Response(
            response: 200,
            description: 'User registration result',
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'responseCode', type: 'integer'),
                ]
            )
        ),
    ]
)]
readonly final class Add implements RequestHandlerInterface {

    public function __construct(
        private UserService              $userService
        , private LoggerInterface        $logger
        , private IUserRepositoryService $userRepositoryService
        , private StringServiceInterface         $stringService
        , private IPaymentService        $paymentService
        , private IPaymentLogRepository  $paymentLogRepository
        , private Application            $application
        , private IConfigService         $configService
        , private IEventService          $eventService
        , private IResponseService       $responseService
        , private ICollectorService      $collectorService
    ) {
    }

    // TODO create a token and forward it to the frontend
    //  in order to prevent multiple user creation

    // TODO restrict numeric usernames only
    // TODO no blanks in usernames
    #[\Override]
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
        $key                = $this->getParameter("key", $request);
        $kdfVersion         = $this->getParameter("kdf_version", $request);
        // TODO fix
        $phone   = '00000000000';
        $website = $this->application->getMetaData()->get('web');

        if (true === $this->stringService->isEmpty($termsAndConditions)) {
            $this->collectorService->addCounter(
                name: 'invalidRegister'
                , labels: ['termsAndConditionsNotChecked']
            );
            $this->logger->info('terms and conditions are not selected', ['termsAndConditions' => $termsAndConditions]);
            return new JsonResponse(
                [
                    'responseCode' => $this->responseService->getResponseCode(IResponseCodes::RESPONSE_NAME_TERMS_AND_CONDITIONS_NOT_AGREED)
                ]
                , IResponse::BAD_REQUEST
            );
        }

        $this->logger->debug('start validating password');
        $resultList = $this->userService->validatePasswords($password, $passwordRepeat);

        if ($resultList->length() > 0) {
            $this->collectorService->addCounter(
                name: 'invalidRegister'
                , labels: ['passwordValidationFailed']
            );
            $this->logger->warning('password validation failed', ['results' => $resultList->toArray()]);
            return new JsonResponse(
                [
                    'responseCode' => $this->responseService->getResponseCode(IResponseCodes::RESPONSE_NAME_INVALID_PASSWORD),
                    'results'      => $resultList->toArray()
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

            $this->collectorService->addCounter(
                name: 'invalidRegister'
                , labels: ['validationFailed']
            );

            return new JsonResponse(
                [
                    'responseCode' => $this->responseService->getResponseCode(IResponseCodes::RESPONSE_NAME_VALIDATE_USER),
                    'results'      => $result->toArray()
                ]
                , IResponse::BAD_REQUEST
            );
        }

        try {
            $this->logger->debug('start creating new user');
            $user = $this->userRepositoryService->createUser($user);
        } catch (Exception $exception) {
            $this->logger->error('error creating new user', ['exception' => $exception]);

            $this->collectorService->addCounter(
                name: 'invalidRegister'
                , labels: ['errorCreatingUser']
            );

            return new JsonResponse(
                [
                    'responseCode' => $this->responseService->getResponseCode(IResponseCodes::RESPONSE_NAME_ERROR_CREATING_USER)
                ]
                , IResponse::INTERNAL_SERVER_ERROR
            );
        }

        if (true === $isSaas) {
            $this->logger->debug('saas mode - creating subscription');
            $sessionId = Uuid::uuid4()->toString();
            $plan      = $this->getParameter('plan', $request);
            if (true === $this->stringService->isEmpty($plan)) {
                $plan = (string) $this->configService->getValue('mollie_default_plan', '');
            }
            $language = $this->getParameter('language', $request);
            if (true === $this->stringService->isEmpty($language)) {
                $language = 'en';
            }

            try {
                $checkout = $this->paymentService->createSubscription(
                    $plan,
                    $email,
                    $sessionId,
                    $language
                );
            } catch (PaymentException $exception) {
                $this->logger->error('saas mode - error creating subscription', ['exception' => $exception]);
                $this->collectorService->addCounter(
                    name: 'invalidRegister'
                    , labels: ['subscriptionCreationFailed']
                );
                return new JsonResponse(
                    [
                        'responseCode' => $this->responseService->getResponseCode(IResponseCodes::RESPONSE_NAME_ERROR_CREATING_USER)
                    ]
                    , IResponse::INTERNAL_SERVER_ERROR
                );
            }

            $log = new Log(
                key: $sessionId,
                log: [
                    'checkoutUrl' => $checkout->getCheckoutUrl(),
                    'paymentId'   => $checkout->getPaymentId(),
                    'customerId'  => $checkout->getCustomerId(),
                    'plan'        => $plan,
                    'user'        => ['id' => $user->getId(), 'email' => $email]
                ],
                // The registration row already records the Mollie customer id
                // (getCustomerId above); the webhook reads it back from here to
                // start recurring billing once the first payment is confirmed.
                createTs: new DateTimeImmutable()
            );

            $this->paymentLogRepository->insert($log);
            $this->logger->debug('saas mode - responding checkout url');

            $this->collectorService->addCounter(
                name: 'invalidRegister'
                , labels: ['saasUserCreated']
            );

            return new JsonResponse(
                [
                    'checkoutUrl'  => $checkout->getCheckoutUrl(),
                    'session'      => $sessionId,
                    'responseCode' => $this->responseService->getResponseCode(IResponseCodes::RESPONSE_NAME_USER_SUBSCRIPTION_CREATED)
                ]
                , IResponse::OK
            );
        }

        $this->eventService->execute(
            new UserRegisteredEvent(
                $user,
                $key,
                $kdfVersion,
                Type::REGULAR,
                1
            )
        );

        $this->collectorService->addCounter(
            name: 'validRegister'
        );

        $this->logger->debug('end add user');
        return new JsonResponse(
            [
                'responseCode' => $this->responseService->getResponseCode(IResponseCodes::RESPONSE_NAME_USER_CREATED)
            ]
            , IResponse::OK
        );
    }

    private function getParameter(string $name, ServerRequestInterface $request): string {
        $body = (array) $request->getParsedBody();
        return (string) ($body[$name] ?? null);
    }

}
