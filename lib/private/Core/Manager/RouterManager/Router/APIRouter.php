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

use DI\DependencyException;
use DI\NotFoundException;
use doganoo\PHPUtil\Log\FileLogger;
use Keestash;
use Keestash\Api\AbstractApi;
use Keestash\Core\DTO\APIRequest;
use Keestash\Exception\NoControllerFoundException;
use KSP\Core\DTO\IToken;
use ReflectionClass;
use ReflectionException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;

class APIRouter extends Router {

    public const FIELD_NAME_USER_HASH = "user_hash";
    public const FIELD_NAME_TOKEN     = "token";

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

            /** @var AbstractApi $service */
            $service       = $instance->newInstanceArgs($constructorArgs);
            $parentClasses = parent::getParentClasses($service);

            $methodParameters = $httpParams + $parameters;
            if (true === $parentClasses->containsValue(AbstractApi::class)) {

                if (true === $service->hasAssociativeIndices()) {
                    $service->onCreate($methodParameters);
                } else {
                    $onCreate = $instance->getMethod("onCreate");
                    $onCreate->invokeArgs($service, $methodParameters);
                }

                $hasPermission = parent::hasPermission(
                    $service->getPermission()
                );

                FileLogger::debug("has permission $hasPermission");

                $start = microtime(true);

                Keestash::getServer()
                    ->getServiceHookManager()
                    ->executePre();

                $service->create();

                Keestash::getServer()
                    ->getSubmitHookManager()
                    ->executePost();

                $service->afterCreate();
                Keestash::getServer()
                    ->getResponseManager()
                    ->add($service->getResponse());

                $end = microtime(true);

                $logged = $this->log(
                    $token
                    , $start
                    , $end
                );

                FileLogger::debug("$start");
                FileLogger::debug("$end");

                FileLogger::debug("logged request: $logged");
                return;
            }

        } catch (DependencyException $exception) {
            FileLogger::error($exception->getMessage());
        } catch (NotFoundException $exception) {
            FileLogger::error($exception->getMessage());
        } catch (ReflectionException $exception) {
            FileLogger::error($exception->getMessage());
        } catch (ResourceNotFoundException $exception) {
            FileLogger::error($exception->getMessage());
            if (Keestash::getMode() === Keestash::MODE_WEB) {
                $this->routeTo("");
            }
        }

        //TODO make this to an ErrorView / a 404 catcher
        throw new NoControllerFoundException("no controller $controller found");
    }

    private function log(?IToken $token, float $start, float $end): bool {
        $route = $this->getRouteName();
        if (null === $token) {
            FileLogger::debug("There is no token for $route which means that it is not necessary to log");
            return true;
        }
        $request = new APIRequest();
        $request->setRoute($route);
        $request->setStart($start);
        $request->setEnd($end);
        $request->setToken($token);

        return parent::logRequest($request);
    }

    public function getRequiredParameter(): array {

        $userId = $this->getParameter(APIRouter::FIELD_NAME_USER_HASH);
        $token  = $this->getParameter(APIRouter::FIELD_NAME_TOKEN);

        return [
            APIRouter::FIELD_NAME_USER_HASH => $userId
            , APIRouter::FIELD_NAME_TOKEN   => $token
        ];

    }

}