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

namespace Keestash\Core\Repository\AppRepository;

use Doctrine\DBAL\Exception;
use doganoo\DI\DateTime\IDateTimeService;
use doganoo\PHPAlgorithms\Datastructure\Table\HashTable;
use Keestash\Core\DTO\App\Config\App;
use Keestash\Exception\App\AppNotFoundException;
use Keestash\Exception\TooManyRowsException;
use KSP\Core\Backend\IBackend;
use KSP\Core\DTO\App\Config\IApp;
use KSP\Core\Repository\AppRepository\IAppRepository;
use Psr\Log\LoggerInterface as ILogger;

class AppRepository implements IAppRepository {

    private IDateTimeService $dateTimeService;
    private IBackend         $backend;
    private ILogger          $logger;

    public function __construct(
        IBackend           $backend
        , IDateTimeService $dateTimeService
        , ILogger          $logger
    ) {
        $this->backend         = $backend;
        $this->dateTimeService = $dateTimeService;
        $this->logger          = $logger;
    }

    public function getAllApps(): HashTable {
        $map = new HashTable();

        $queryBuilder = $this->backend->getConnection()->createQueryBuilder();
        $queryBuilder->select(
            [
                'app_id'
                , 'enabled'
                , 'create_ts'
                , 'version'
            ]
        )
            ->from('app_config');
        $result = $queryBuilder->executeQuery();
        $users  = $result->fetchAllNumeric();

        foreach ($users as $row) {

            $appId    = $row[0];
            $enabled  = $row[1];
            $createTs = $row[2];
            $version  = $row[3];

            $app = new App();
            $app->setId($appId);
            $app->setEnabled($enabled === IApp::ENABLED_TRUE);
            $app->setVersion((int) $version);
            $app->setCreateTs($this->dateTimeService->fromFormat($createTs));

            $map->put($app->getId(), $app);
        }

        return $map;
    }

    public function getApp(string $id): IApp {
        try {
            $app          = null;
            $queryBuilder = $this->backend->getConnection()->createQueryBuilder();
            $queryBuilder->select(
                [
                    'app_id'
                    , 'enabled'
                    , 'create_ts'
                    , 'version'
                ]
            )
                ->from('app_config')
                ->where('app_id = ?')
                ->setParameter(0, $id);
            $result    = $queryBuilder->executeQuery();
            $users     = $result->fetchAllNumeric();
            $userCount = count($users);

            if (0 === $userCount) {
                throw new AppNotFoundException();
            }

            if ($userCount > 1) {
                throw new TooManyRowsException();
            }

            foreach ($users as $row) {

                $appId    = $row[0];
                $enabled  = $row[1];
                $createTs = $row[2];
                $version  = $row[3];

                $app = new App();
                $app->setId($appId);
                $app->setEnabled($enabled === IApp::ENABLED_TRUE);
                $app->setVersion((int) $version);
                $app->setCreateTs($this->dateTimeService->fromFormat($createTs));

            }
            return $app;
        } catch (Exception|TooManyRowsException $exception) {
            $this->logger->error('error while getting app', ['exception' => $exception, 'id' => $id]);
            throw new AppNotFoundException();
        }
    }

    public function replace(IApp $app): bool {
        // notice that we can not use any doctrine
        // support here as this seems to be an
        // MySQL only thing: https://stackoverflow.com/a/4561615/1966490
        $sql = "
                replace into `app_config` (
                                          `app_id`
                                          , `enabled`
                                          , `version`
                                          )
                values (
                        '" . $app->getId() . "',
                        '" . ($app->isEnabled() ? "true" : "false") . "',
                        '" . $app->getVersion() . "'
                );
        ";
        $this->backend->getConnection()->prepare($sql)->executeStatement();
        // There is otherwise an exception thrown.
        // Do not know how to handle this better for now
        return true;
    }

}
