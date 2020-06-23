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
use doganoo\PHPUtil\Log\FileLogger;
use Keestash;
use Keestash\Api\AbstractApi;
use Keestash\Core\DTO\APIRequest;
use Keestash\Exception\KeestashException;
use Keestash\Exception\NoControllerFoundException;
use KSP\Core\DTO\IToken;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

class APIRouter extends Router {

    public const FIELD_NAME_USER_HASH = "user_hash";
    public const FIELD_NAME_TOKEN     = "token";

    /**
     * @param IToken|null $token
     * @throws KeestashException
     * @throws NoControllerFoundException
     */
    public function route(?IToken $token): void {
        try {
            /** @var array $allParameters */
            $allParameters = $this->getAllParameters();
            /** @var AbstractApi $service */
            $service = $this->getReflectionService()->createObject(
                $this->getControllerName()
                , $token
            );

            /** @var ArrayList $parentClasses */
            $parentClasses = $this->getReflectionService()->getParentClasses($service);

            if (false === $parentClasses->containsValue(AbstractApi::class)) {
                throw new KeestashException("passed controller is not an instance of AbstractApi");
            }

            $service->setParameters($allParameters);
            $service->onCreate($allParameters);

            $hasPermission = $this->hasPermission(
                $service->getPermission()
            );

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

            return;

        } catch (ResourceNotFoundException $exception) {
            FileLogger::error($exception->getMessage());
        }

        //TODO make this to an ErrorView / a 404 catcher
        throw new NoControllerFoundException("no controller found");
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
