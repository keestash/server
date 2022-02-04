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
use Keestash\Core\Service\HTTP\PersistenceService;
use KSP\Core\Controller\StaticAppController;
use KSP\Core\DTO\User\IUser;
use KSP\Core\Repository\User\IUserRepository;
use KSP\Core\Service\Controller\IAppRenderer;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Http\Message\ServerRequestInterface;

class Login extends StaticAppController {

    private PersistenceService        $persistenceService;
    private InstanceDB                $instanceDb;
    private IUserRepository           $userRepository;
    private TemplateRendererInterface $templateRenderer;

    public function __construct(
        PersistenceService          $persistenceService
        , InstanceDB                $instanceDB
        , IAppRenderer              $appRenderer
        , IUserRepository           $userRepository
        , TemplateRendererInterface $templateRenderer
    ) {
        $this->persistenceService = $persistenceService;
        $this->instanceDb         = $instanceDB;
        $this->userRepository     = $userRepository;
        $this->templateRenderer   = $templateRenderer;

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
