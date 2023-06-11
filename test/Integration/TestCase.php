<?php
declare(strict_types=1);

namespace KST\Integration;

use Exception;
use JsonException;
use Keestash\ConfigProvider;
use Keestash\Core\Service\Router\VerificationService;
use KSA\PasswordManager\Test\Service\RequestService;
use KSA\PasswordManager\Test\Service\ResponseService;
use KSP\Api\IVerb;
use KSP\Core\DTO\User\IUser;
use KSP\Core\Repository\User\IUserRepository;
use KSP\Core\Service\Encryption\IStringMaskService;
use KST\Service\Exception\KSTException;
use KST\Service\Service\UserService;
use Laminas\Config\Config;
use Mezzio\Application;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Application as ConsoleApplication;
use Symfony\Component\Console\Tester\CommandTester;

abstract class TestCase extends \KST\TestCase {

    private Application         $application;
    private ResponseService     $responseService;
    private RequestService      $requestService;
    private Config              $config;
    private ?ConsoleApplication $consoleApplication = null;
    private IStringMaskService  $stringMaskService;

    protected function setUp(): void {
        parent::setUp();
        $this->application       = $this->getService(Application::class);
        $this->responseService   = $this->getService(ResponseService::class);
        $this->requestService    = $this->getService(RequestService::class);
        $this->config            = $this->getService(Config::class);
        $this->stringMaskService = $this->getService(IStringMaskService::class);

        (require __DIR__ . '/../../lib/config/pipeline/api/pipeline.php')($this->application);
        $router = $this->config->get(ConfigProvider::API_ROUTER);

        /** @var Config $route */
        foreach ($router[ConfigProvider::ROUTES] as $route) {
            $method     = strtolower((string) $route->get('method'));
            $middleware = $route->get('middleware');
            $name       = $route->get('name');
            $path       = $route->get('path');

            if ($middleware instanceof Config) {
                $middleware = $middleware->toArray();
            }

            switch ($method) {
                case IVerb::GET:
                    $this->application->get(
                        $path
                        , $middleware
                        , $name
                    );
                    break;
                case IVerb::POST:
                    $this->application->post(
                        $path
                        , $middleware
                        , $name
                    );
                    break;
                case IVerb::PUT:
                    $this->application->put(
                        $path
                        , $middleware
                        , $name
                    );
                    break;
                case IVerb::DELETE:
                    $this->application->delete(
                        $path
                        , $middleware
                        , $name
                    );
                    break;
                default:
                    throw new Exception('unknown method ' . $method);
            }
        }
    }

    protected function getCommandTester(string $signature): CommandTester {

        if (null === $this->consoleApplication) {
            $this->consoleApplication = new ConsoleApplication(
                'Keestash Testing'
                , '1.0.0'
            );

            foreach ($this->config->get(ConfigProvider::COMMANDS)->toArray() as $commandClass) {
                $this->consoleApplication->add(
                    $this->getService($commandClass)
                );
            }
        }

        return new CommandTester(
            $this->consoleApplication->find($signature)
        );
    }

    protected function login(IUser $user, string $password): array {
        $response = $this->getApplication()->handle(
            $this->getRequest(
                IVerb::POST
                , '/login/submit'
                , [
                'user'       => $user->getName()
                , 'password' => $password
            ],
                $user
            )
        );

        if (
            false === $response->hasHeader(VerificationService::FIELD_NAME_TOKEN)
            || false === $response->hasHeader(VerificationService::FIELD_NAME_USER_HASH)
        ) {
            throw new KSTException(
                sprintf(
                    'hash or token not given, %s %s'
                    , $response->getBody()
                    , $response->getStatusCode()
                )
            );
        }

        $token = $response->getHeader(VerificationService::FIELD_NAME_TOKEN)[0] ?? null;
        $hash  = $response->getHeader(VerificationService::FIELD_NAME_USER_HASH)[0] ?? null;

        if (
            null === $token
            || null == $hash
        ) {
            throw new KSTException(
                sprintf(
                    'hash or token are null: %s, %s, %s, %s'
                    , $this->stringMaskService->mask($hash)
                    , $this->stringMaskService->mask($token)
                    , $response->getBody()
                    , $response->getStatusCode()
                )
            );
        }

        return [
            VerificationService::FIELD_NAME_TOKEN       => $token
            , VerificationService::FIELD_NAME_USER_HASH => $hash
        ];
    }

    protected function logout(array $headers, IUser $user): void {
        $this->getApplication()->handle(
            $this->getRequest(
                IVerb::POST
                , '/logout/submit'
                , []
                , $user
                , $headers
            )
        );
    }

    public function getRequest(
        string   $verb
        , string $path
        , array  $body
        , ?IUser $user = null
        , array  $keestashHeaders = []
        , array  $files = []
    ): ServerRequestInterface {

        if (null === $user) {
            return $this->getRequestService()
                ->getRequest(
                    $verb
                    , $path
                    , $body
                    , $keestashHeaders
                    , $files
                );
        }
        return $this->getRequestService()
            ->getRequestWithToken(
                $user
                , $verb
                , $path
                , $body
                , $keestashHeaders
                , $files
            );
    }

    protected function getApplication(): Application {
        return $this->application;
    }

    protected function logResponse(ResponseInterface $response): void {
        /** @var LoggerInterface $logger */
        $logger = $this->getService(LoggerInterface::class);
        $logger->debug('response', [
            'status'    => $response->getStatusCode()
            , 'body'    => (string) $response->getBody()
            , 'headers' => $response->getHeaders()
        ]);
    }


    protected function getResponseService(): ResponseService {
        return $this->responseService;
    }

    protected function getRequestService(): RequestService {
        return $this->requestService;
    }

    /**
     * @param array $body
     * @return ServerRequestInterface
     * @deprecated
     */
    protected function getVirtualRequest(array $body = [], ?IUser $user = null): ServerRequestInterface {

        if (null === $user) {
            $user = $this->getService(IUserRepository::class)
                ->getUserById((string) UserService::TEST_USER_ID_2);
        }

        return $this->getRequestService()->getVirtualRequestWithToken(
            $user
            , []
            , []
            , $body
        );
    }

    /**
     * @param ResponseInterface $response
     * @return array
     * @throws JsonException
     */
    protected function getResponseBody(ResponseInterface $response): array {
        return (array) json_decode(
            (string) $response->getBody()
            , true
            , 512
            , JSON_THROW_ON_ERROR
        );
    }

    public function assertValidResponse(ResponseInterface $response): void {
        $this->assertTrue(true === $this->responseService->isValidResponse($response));
    }

    public function assertStatusCode(int $statusCode, ResponseInterface $response): void {
        $this->assertTrue($statusCode === $response->getStatusCode());
    }

    public function getDecodedData(ResponseInterface $response): array {
        return json_decode(
            (string) $response->getBody()
            , true
            , 512
            , JSON_THROW_ON_ERROR
        );
    }

}