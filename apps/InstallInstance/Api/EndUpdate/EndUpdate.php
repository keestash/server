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

namespace KSA\InstallInstance\Api\EndUpdate;

use Keestash\Api\Response\LegacyResponse;
use Keestash\Core\Service\File\FileService;
use Keestash\Core\Service\HTTP\HTTPService;
use Keestash\Core\Service\HTTP\PersistenceService;
use Keestash\Core\Service\Instance\InstallerService;
use Keestash\Core\System\Installation\Instance\LockHandler;
use KSA\InstallInstance\Exception\InstallInstanceException;
use KSP\Api\IResponse;
use KSP\App\ILoader;
use KSP\Core\ILogger\ILogger;
use KSP\Core\Repository\File\IFileRepository;
use KSP\Core\Repository\User\IUserRepository;
use KSP\Core\Service\User\IUserService;
use KSP\Core\Service\User\Repository\IUserRepositoryService;
use KSP\L10N\IL10N;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class EndUpdate implements RequestHandlerInterface {

    private InstallerService       $installerService;
    private LockHandler            $lockHandler;
    private IFileRepository        $fileRepository;
    private FileService            $fileService;
    private IUserService           $userService;
    private IUserRepository        $userRepository;
    private PersistenceService     $persistenceService;
    private HTTPService            $httpService;
    private IL10N                  $translator;
    private ILoader                $loader;
    private ILogger                $logger;
    private IUserRepositoryService $userRepositoryService;

    public function __construct(
        IL10N $l10n
        , InstallerService $installerService
        , LockHandler $lockHandler
        , IFileRepository $fileRepository
        , FileService $fileService
        , IUserService $userService
        , IUserRepository $userRepository
        , PersistenceService $persistenceService
        , HTTPService $httpService
        , ILoader $loader
        , ILogger $logger
        , IUserRepositoryService $userRepositoryService
    ) {
        $this->installerService      = $installerService;
        $this->lockHandler           = $lockHandler;
        $this->fileRepository        = $fileRepository;
        $this->fileService           = $fileService;
        $this->userService           = $userService;
        $this->userRepository        = $userRepository;
        $this->persistenceService    = $persistenceService;
        $this->httpService           = $httpService;
        $this->translator            = $l10n;
        $this->loader                = $loader;
        $this->logger                = $logger;
        $this->userRepositoryService = $userRepositoryService;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface {

        $isInstalled = $this->installerService->isInstalled();

        if (false === $isInstalled) {
            return LegacyResponse::fromData(
                IResponse::RESPONSE_CODE_NOT_OK
                , [
                    "message" => $this->translator->translate("The instance is not finally installed. Aborting (2)")
                ]
            );
        }

        $ran     = $this->installerService->runCoreMigrations();
        $removed = $this->installerService->removeInstaller();
        $added   = false;

        if (
            true === $ran
            && true === $removed
        ) {
            $added = $this->installerService->writeIdAndHash();
            // $this->installerService->writeProductionMode(); TODO not sure actually how to enable, need to differentiate between dev mode and production
        }

        if (false === $added) {
            return LegacyResponse::fromData(
                IResponse::RESPONSE_CODE_NOT_OK
                , [
                    "message" => $this->translator->translate("Could not do final steps")
                    , "added" => $added
                ]
            );
        }

        $this->lockHandler->unlock();
        $this->persistenceService->killAll();

        $this->userRepositoryService->createSystemUser(
            $this->userService->getSystemUser()
        );

        $this->fileRepository->add(
            $this->fileService->getDefaultImage()
        );

        $defaultApp = $this->loader->getDefaultApp();

        if (null === $defaultApp) {
            throw new InstallInstanceException();
        }

        return LegacyResponse::fromData(
            IResponse::RESPONSE_CODE_OK
            , [
                "message"    => "Ok"
                , "route_to" => $this->httpService->buildWebRoute(
                    $defaultApp->getBaseRoute()
                )
            ]
        );

    }

}
