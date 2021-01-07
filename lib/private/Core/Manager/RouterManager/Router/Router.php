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

use doganoo\PHPAlgorithms\Datastructure\Lists\ArrayList\ArrayList;
use doganoo\PHPAlgorithms\Datastructure\Table\HashTable;
use Exception;
use Keestash;
use Keestash\Core\Service\ReflectionService;
use KSP\Core\DTO\Instance\Request\IAPIRequest;
use KSP\Core\DTO\Token\IToken;
use KSP\Core\ILogger\ILogger;
use KSP\Core\Manager\RouterManager\IRouter;
use KSP\Core\Permission\IPermission;
use KSP\Core\Repository\ApiLog\IApiLogRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

abstract class Router implements IRouter {

    public const FIELD_NAME_CONTROLLER = "controller";

    protected RouteCollection $routes;
    private HashTable         $publicRoutes;
    private IApiLogRepository $apiLoggerManager;
    private ReflectionService $reflectionService;
    private ILogger           $logger;

    public function __construct(
        IApiLogRepository $apiLoggerManager
        , ReflectionService $reflectionService
        , ILogger $logger
    ) {
        $this->routes           = new RouteCollection();
        $this->publicRoutes     = new HashTable();
        $this->apiLoggerManager = $apiLoggerManager;
        $this->registerPublicRoute("/");
        $this->reflectionService = $reflectionService;
        $this->logger            = $logger;
    }

    public function registerPublicRoute(string $name): bool {
        $this->publicRoutes->put($name, true);
        $this->publicRoutes->put("$name/", true);
        return true;
    }

    public function addRoute(string $name, array $defaults, array $verbs = [IRouter::GET]): bool {
        $this->add($name, $defaults, $verbs);
        $this->withSlash($name, $defaults, $verbs);
        return true;
    }

    private function add(string $name, array $defaults, array $verbs): void {
        $route = new Route(
            $name
            , $defaults
            , []
            , []
            , ""
            , []
            , $verbs
        );
        $this->routes->add($name, $route);
    }

    private function withSlash(
        string $name
        , array $defaults
        , array $verbs
    ): void {
        if (substr($name, -1, 1) === "/") return;
        $this->add("$name/", $defaults, $verbs);
    }

    public function hasRoute(?string $name): bool {
        return null !== $this->getRoute($name);
    }

    public function getRoute(?string $name): ?Route {
        return $this->routes->get($name);
    }

    public function getRoutes(): ?HashTable {
        if (null === $this->routes) return null;
        $table = new HashTable();
        foreach ($this->routes->all() as $key => $route) {
            $table->put($route->getPath(), $route);
        }
        return $table;
    }

    public function isPublicRoute(): bool {
        return $this->getPublicRoutes()->containsKey(
            $this->getRouteName()
        );
    }

    public function getPublicRoutes(): HashTable {
        return $this->publicRoutes;
    }

    public function getRouteName(): ?string {
        $route = null;
        try {
            $context = new RequestContext();
            $request = Request::createFromGlobals();
            $context->fromRequest($request);
            $matcher    = new UrlMatcher($this->routes, $context);
            $parameters = $matcher->match($context->getPathInfo());
            $route      = $parameters["_route"];
        } catch (Exception $exception) {
//            $this->logger->error($exception->getMessage() . " " . $exception->getTraceAsString());
            return null;
        }
        return $route;
    }

    abstract public function route(?IToken $token): void;

    protected function getControllerName(): string {
        return $this->getParameter(Router::FIELD_NAME_CONTROLLER);
    }

    public function getParameter(string $name): ?string {
        $allParameters = $this->getAllParameters();
        return $allParameters[$name] ?? null;
    }

    protected function getAllParameters(): array {
        $globals = $this->getParametersFromGlobals();
        $route   = $this->getRouteParameters();

        return array_merge(
            $globals
            , $route
        );

    }

    protected function getParametersFromGlobals(): array {
        return array_merge(
            $_GET ?? []
            , $_POST ?? []
            , $_FILES ?? []
            , $_SERVER ?? []
            , $_SESSION ?? []
            , $_COOKIE ?? []
        );
    }

    protected function getRouteParameters(): array {
        $context = new RequestContext();
        $request = Request::createFromGlobals();
        $context->fromRequest($request);
        $matcher = new UrlMatcher($this->routes, $context);
        return $matcher->match($context->getPathInfo());
    }

    protected function logRequest(IAPIRequest $request): bool {
        $logRequests = Keestash::getServer()->getConfig()->get("log_requests");
        if (false === $logRequests) return false;
        return null !== $this->apiLoggerManager->log($request);
    }

    protected function getParentClasses($class): ArrayList {
        $list = new ArrayList();
        $p    = class_parents($class);
        $list->addAllArray($p);
        return $list;
    }

    protected function getReflectionService(): ReflectionService {
        return $this->reflectionService;
    }

    protected function hasPermission(?IPermission $permission): bool {
        // for the case that the instance is not installed,
        // we get an exception here. Need to fix this
        // TODO FIXME
        return true;
        $permissionHandler = Keestash::getServer()->getPermissionHandler();
        return $permissionHandler->hasPermission($permission);
    }

}
