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

use DateTime;
use DateTimeImmutable;
use Keestash\Api\Response\OkResponse;
use Keestash\Core\DTO\User\NullUserState;
use KSA\Register\Entity\IResponseCodes;
use KSP\Api\IResponse;
use KSP\Core\DTO\User\IUserState;
use KSP\Core\Repository\User\IUserStateRepository;
use KSP\Core\Service\HTTP\IResponseService;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ResetPasswordRetrieve implements RequestHandlerInterface {

    public function __construct(
        private readonly IUserStateRepository $userStateRepository
        , private readonly IResponseService   $responseService
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface {
        $hash = (string) $request->getAttribute('hash');

        $userState = $this->findCandidate($hash);

        if ($userState instanceof NullUserState) {
            return new JsonResponse(
                [
                    "responseCode" => $this->responseService->getResponseCode(IResponseCodes::RESPONSE_NAME_RESET_PASSWORD_RETRIEVE_USER_BY_HASH_NOT_FOUND)
                ]
                , IResponse::NOT_FOUND
            );
        }

        $difference = $userState->getCreateTs()->diff(new DateTimeImmutable());
        if ($difference->i < 2) {
            return new JsonResponse(
                [
                    "responseCode" => $this->responseService->getResponseCode(IResponseCodes::RESPONSE_NAME_RESET_PASSWORD_RETRIEVE_USER_BY_HASH_EXPIRED)
                ]
                , IResponse::NOT_FOUND
            );

        }

        return new OkResponse(
            [
                "user" => [
                    "name" => $userState->getUser()->getName()
                ]
            ]
        );
    }

    private function findCandidate(string $hash): IUserState {
        $userState = $this->userStateRepository->getByHash($hash);
        if ($userState->getCreateTs()->diff(new DateTime())->i < 2) {
            return $userState;
        }
        return new NullUserState();
    }

}
