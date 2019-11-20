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

use doganoo\PHPAlgorithms\Datastructure\Lists\ArrayLists\ArrayList;
use doganoo\PHPAlgorithms\Datastructure\Table\HashTable;
use Keestash;
use Keestash\Core\Service\ReflectionService;
use KSP\Core\DTO\IAPIRequest;
use KSP\Core\DTO\IToken;
use KSP\Core\Manager\RouterManager\IRouter;
use KSP\Core\Permission\IPermission;
use KSP\Core\Repository\ApiLog\IApiLogRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

abstract class Router implements IRouter {

    public const FIELD_NAME_CONTROLLER = "controller";

    protected $routes            = null;
    private   $allowedRoutes     = null;
    private   $apiLoggerManager  = null;
    private   $reflectionService = null;

    public function __construct(
        IApiLogRepository $apiLoggerManager
        , ReflectionService $reflectionService
    ) {
        $this->routes           = new RouteCollection();
        $this->allowedRoutes    = new HashTable();
        $this->apiLoggerManager = $apiLoggerManager;
        $this->registerPublicRoute("/");
        $this->reflectionService = $reflectionService;
    }

    public function registerPublicRoute(string $name): bool {
        $this->allowedRoutes->put($name, true);
        $this->allowedRoutes->put("$name/", true);
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

    protected function getControllerName(): string {
        return $this->getParameter(Router::FIELD_NAME_CONTROLLER);
    }

    protected function getParametersFromGlobals(): array {
        $globals = array_merge(
            $_GET ?? []
            , $_POST ?? []
            , $_FILES ?? []
            , $_SERVER ?? []
            , $_SESSION ?? []
            , $_COOKIE ?? []
        );

        return $globals;
    }

    protected function getRouteParameters(): array {
        $context = new RequestContext();
        $request = Request::createFromGlobals();
        $context->fromRequest($request);
        $matcher    = new UrlMatcher($this->routes, $context);
        $parameters = $matcher->match($context->getPathInfo());
        return $parameters;
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
        return $this->allowedRoutes->containsKey(
            $this->getRouteName()
        );
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
        } catch (ResourceNotFoundException $exception) {
//            FileLogger::error($exception->getMessage() . " " . $exception->getTraceAsString());
            return null;
        }
        return $route;
    }

    abstract public function route(?IToken $token): void;

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

    protected function hasPermission(?IPermission $permission) {
        return true;
        $permissionHandler = Keestash::getServer()->getPermissionHandler();
        return $permissionHandler->hasPermission($permission);
    }

}