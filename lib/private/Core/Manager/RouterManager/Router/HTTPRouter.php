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

namespace Keestash\Core\Manager\RouterManager\Router;

use doganoo\PHPUtil\Log\FileLogger;
use Keestash;
use Keestash\Core\Service\ReflectionService;
use KSP\Core\Controller\AppController;
use KSP\Core\Controller\IAppController;
use KSP\Core\DTO\IToken;
use KSP\Core\Repository\ApiLog\IApiLogRepository;
use ReflectionClass;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;

class HTTPRouter extends Router {

    private $routeType = null;

    public function __construct(
        IApiLogRepository $loggerManager
        , ReflectionService $reflectionService
    ) {
        $this->routeType = IAppController::CONTROLLER_TYPE_NORMAL;
        parent::__construct(
            $loggerManager
            , $reflectionService
        );
    }

    public function route(?IToken $token): void {

        try {
            $context = new RequestContext();
            $request = Request::createFromGlobals();
            $context->fromRequest($request);
            $matcher    = new UrlMatcher($this->routes, $context);
            $parameters = $matcher->match($context->getPathInfo());
            $httpParams = $request->request->all();

            $controller = $parameters["controller"];
            $routeName  = $parameters["_route"];

            $parameters["route"] = $routeName;

            unset($parameters["controller"]);
            unset($parameters["_route"]);

            $constructorArgs = [];
            $instance        = new ReflectionClass($controller);

            if (null !== $instance->getConstructor()) {
                foreach ($instance->getConstructor()->getParameters() as $parameter) {
                    if (true === $parameter->isDefaultValueAvailable()) continue; // TODO validate ?!
                    $className         = $parameter->getClass()->getName();
                    $class             = Keestash::getServer()->query($className);
                    $constructorArgs[] = $class;
                }
            }

            /** @var AppController $controller */
            $controller = $instance->newInstanceArgs($constructorArgs);

            $parentClasses    = parent::getParentClasses($controller);
            $methodParameters = $httpParams + $parameters;

            if ($parentClasses->containsValue(AppController::class)) {

                $controller->setParameters($methodParameters);
                $controller->onCreate(
                    $methodParameters
                );

                $hasPermission = parent::hasPermission(
                    $controller->getPermission()
                );

                Keestash::getServer()
                    ->getControllerHookManager()
                    ->executePre();
                $controller->create();

                Keestash::getServer()
                    ->getControllerHookManager()
                    ->executePost();
                $controller->afterCreate();
                $this->setRouteType($controller->getControllerType());
                return;
            }

        } catch (ResourceNotFoundException $exception) {
            FileLogger::error($exception->getMessage());
        }

        $defaultApp = Keestash::getServer()->getAppLoader()->getDefaultApp();

        if (null !== $defaultApp) {
            $this->routeTo(
                $defaultApp->getBaseRoute()
            );
            exit();
            die();
        }

        $this->routeTo("error_view");
    }

    public function routeTo(string $url): void {
        $baseUrl = Keestash::getBaseURL();
        $url     = "$baseUrl/$url";
        $this->routeURL(strtolower($url));
    }

    private function routeURL(string $url): void {
        header("Location: $url");
        exit();
    }

    private function setRouteType(int $routeType): void {
        $this->routeType = $routeType;
    }

    public function getRouteType(): int {
        return $this->routeType;
    }

}
