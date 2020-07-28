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

use Keestash;
use Keestash\Api\AbstractApi;
use Keestash\Core\Permission\PermissionFactory;
use Keestash\Core\Service\File\FileService;
use Keestash\Core\Service\HTTP\HTTPService;
use Keestash\Core\Service\HTTP\PersistenceService;
use Keestash\Core\Service\Instance\InstallerService;
use Keestash\Core\Service\User\UserService;
use Keestash\Core\System\Installation\Instance\LockHandler;
use KSP\Api\IResponse;
use KSP\Core\DTO\Token\IToken;
use KSP\Core\Repository\File\IFileRepository;
use KSP\Core\Repository\User\IUserRepository;
use KSP\L10N\IL10N;

class EndUpdate extends AbstractApi {

    private $installerService   = null;
    private $lockHandler        = null;
    private $fileRepository     = null;
    private $fileService        = null;
    private $userService        = null;
    private $userRepository     = null;
    private $persistenceService = null;
    private $httpService        = null;

    public function __construct(
        IL10N $l10n
        , InstallerService $installerService
        , LockHandler $lockHandler
        , IFileRepository $fileRepository
        , FileService $fileService
        , UserService $userService
        , IUserRepository $userRepository
        , PersistenceService $persistenceService
        , HTTPService $httpService
        , ?IToken $token = null
    ) {
        parent::__construct($l10n, $token);

        $this->installerService   = $installerService;
        $this->lockHandler        = $lockHandler;
        $this->fileRepository     = $fileRepository;
        $this->fileService        = $fileService;
        $this->userService        = $userService;
        $this->userRepository     = $userRepository;
        $this->persistenceService = $persistenceService;
        $this->httpService        = $httpService;
    }

    public function onCreate(array $parameters): void {

        parent::setPermission(
            PermissionFactory::getDefaultPermission()
        );
    }

    public function create(): void {

        $isInstalled = $this->installerService->isInstalled();

        if (false === $isInstalled) {
            $this->createAndSetResponse(
                IResponse::RESPONSE_CODE_NOT_OK
                , [
                    "message" => $this->getL10N()->translate("The instance is not finally installed. Aborting (2)")
                ]
            );
            return;
        }

        $ran     = $this->installerService->runCoreMigrations();
        $removed = $this->installerService->removeInstaller();
        $added   = false;

        if (true === $ran && true === $removed) {
            $added = $this->installerService->writeIdAndHash();
        }

        if (false === $added) {
            $this->createAndSetResponse(
                IResponse::RESPONSE_CODE_NOT_OK
                , [
                    "message" => $this->getL10N()->translate("Could not do final steps")
                ]
            );
            return;
        }

        $this->lockHandler->unlock();
        $this->persistenceService->killAll();

        $this->userService->createSystemUser(
            $this->userService->getSystemUser()
        );

        parent::createAndSetResponse(
            IResponse::RESPONSE_CODE_OK
            , [
                "message"    => "Ok"
                , "route_to" => $this->httpService->buildWebRoute(
                    Keestash::getServer()->getAppLoader()->getDefaultApp()->getBaseRoute()
                )
            ]
        );
        return;

    }

    public function afterCreate(): void {

    }

}
