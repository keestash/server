<?php
declare(strict_types=1);
/**
 * Keestash
 *
 * Copyright (C) <2023> <Dogan Ucar>
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

namespace KSA\PasswordManager\Api\Node\Pwned;

use DateTimeImmutable;
use Keestash\Api\Response\JsonResponse;
use KSA\Settings\Entity\UserSetting;
use KSA\Settings\Exception\SettingNotFoundException;
use KSA\Settings\Repository\IUserSettingRepository;
use KSP\Api\IResponse;
use KSP\Core\DTO\Token\IToken;
use KSP\Core\DTO\User\IUser;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;

class ChangeState implements RequestHandlerInterface {

    public const USER_SETTING_PWNED_ACTIVE = 'active.pwned.setting.user';

    public function __construct(
        private readonly IUserSettingRepository $userSettingRepository
        , private readonly LoggerInterface      $logger
    ) {
    }

    #[\Override]
    public function handle(ServerRequestInterface $request): ResponseInterface {
        $parsedBody = (array) $request->getParsedBody();
        $activate   = $parsedBody['activate'];

        $user    = $request->getAttribute(IToken::class)->getUser();
        $setting = null;
        try {
            $setting = $this->userSettingRepository->get(ChangeState::USER_SETTING_PWNED_ACTIVE, $user);
        } catch (SettingNotFoundException $e) {
            $this->logger->debug('no setting found. Processing', ['exception' => $e]);
            $setting = null;
        }

        if (true === $activate) {
            return $this->activate($user, null !== $setting);
        }

        return $this->deactivate($user, null !== $setting);
    }

    private function activate(IUser $user, bool $active): ResponseInterface {

        if (true === $active) {
            return new JsonResponse(['active' => true], IResponse::CONFLICT);
        }

        $this->userSettingRepository->add(
            new UserSetting(
                ChangeState::USER_SETTING_PWNED_ACTIVE
                , $user
                , 'active'
                , new DateTimeImmutable()
            )
        );
        return new JsonResponse(['active' => true], IResponse::CREATED);
    }

    private function deactivate(IUser $user, bool $active): ResponseInterface {
        if (false === $active) {
            return new JsonResponse(['active' => false], IResponse::CONFLICT);
        }
        $this->userSettingRepository->remove(
            new UserSetting(
                ChangeState::USER_SETTING_PWNED_ACTIVE
                , $user
                , 'active'
                , new DateTimeImmutable()
            )
        );
        return new JsonResponse(['active' => false], IResponse::CREATED);
    }

}