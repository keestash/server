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

namespace KSA\Login\Controller;

use doganoo\PHPAlgorithms\Datastructure\Table\HashTable;
use Keestash\Core\Repository\Instance\InstanceDB;
use Keestash\Core\Service\Config\ConfigService;
use Keestash\Core\Service\HTTP\HTTPService;
use Keestash\Core\Service\HTTP\PersistenceService;
use Keestash\Legacy\Legacy;
use KSA\Register\ConfigProvider;
use KSP\App\ILoader;
use KSP\Core\Controller\StaticAppController;
use KSP\Core\DTO\User\IUser;
use KSP\Core\Repository\User\IUserRepository;
use KSP\Core\Service\Controller\IAppRenderer;
use KSP\L10N\IL10N;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Http\Message\ServerRequestInterface;

class LoginController extends StaticAppController {

    private ILoader                   $loader;
    private PersistenceService        $persistenceService;
    private Legacy                    $legacy;
    private ConfigService             $configService;
    private InstanceDB                $instanceDb;
    private IUserRepository           $userRepository;
    private TemplateRendererInterface $templateRenderer;
    private IL10N                     $translator;
    private HTTPService               $httpService;

    public function __construct(
        IL10N                       $translator
        , ILoader                   $loader
        , PersistenceService        $persistenceService
        , Legacy                    $legacy
        , ConfigService             $configService
        , InstanceDB                $instanceDB
        , IAppRenderer              $appRenderer
        , IUserRepository           $userRepository
        , TemplateRendererInterface $templateRenderer
        , HTTPService               $httpService
    ) {
        $this->loader             = $loader;
        $this->persistenceService = $persistenceService;
        $this->legacy             = $legacy;
        $this->configService      = $configService;
        $this->instanceDb         = $instanceDB;
        $this->userRepository     = $userRepository;
        $this->templateRenderer   = $templateRenderer;
        $this->translator         = $translator;
        $this->httpService        = $httpService;

        parent::__construct($appRenderer);
    }

    public function run(ServerRequestInterface $request): string {
        $userId = $this->persistenceService->getValue("user_id");
        $users  = $this->userRepository->getAll();
        $hashes = new HashTable();

        /** @var IUser $user */
        foreach ($users as $user) {
            $hashes->put(
                $user->getHash()
                , $user->getId()
            );
        }

        if (null !== $userId && $hashes->containsValue((int) $userId)) {
            // TODO redirect to $this->loader->getDefaultApp()->getBaseRoute()
        }

        $isDemoMode = $this->instanceDb->getOption("demo") === "true";
        $demo       = $isDemoMode
            ? md5(uniqid())
            : null;

        return $this->templateRenderer
            ->render('login::login');

    }

}
