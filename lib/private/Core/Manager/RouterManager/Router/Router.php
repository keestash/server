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
use doganoo\PHPUtil\Log\FileLogger;
use Keestash;
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

    protected $routes           = null;
    private   $allowedRoutes    = null;
    private   $apiLoggerManager = null;

    public function __construct(IApiLogRepository $apiLoggerManager) {
        $this->routes           = new RouteCollection();
        $this->allowedRoutes    = new HashTable();
        $this->apiLoggerManager = $apiLoggerManager;
        $this->registerPublicRoute("/");
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

        $parameters = array_merge(
            $_GET
            , $_POST
            , $_COOKIE
            , $_FILES
            , $_SERVER
        );

        return $parameters[$name] ?? null;
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

    protected function hasPermission(?IPermission $permission) {
        return true;
        $permissionHandler = Keestash::getServer()->getPermissionHandler();
        return $permissionHandler->hasPermission($permission);
    }

    protected function getHttpParameters(): array {
        $request = Request::createFromGlobals();
        return $request->request->all();
    }

    protected function getUrlParameter(): array {
        $context = new RequestContext();
        $context->fromRequest(
            Request::createFromGlobals()
        );
        $matcher = new UrlMatcher($this->routes, $context);
        return $matcher->match($context->getPathInfo());
    }

    protected function handleParameters(): array {
        $parameters = $this->getUrlParameter();
        $httpParams = $this->getHttpParameters();
        return $httpParams + $parameters;
    }

}