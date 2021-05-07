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

use doganoo\DI\DateTime\IDateTimeService;
use doganoo\PHPAlgorithms\Datastructure\Table\HashTable;
use Keestash\App\Config\App;
use Keestash\Core\Repository\AbstractRepository;
use KSP\App\Config\IApp;
use KSP\Core\Backend\IBackend;
use KSP\Core\Repository\AppRepository\IAppRepository;

class AppRepository extends AbstractRepository implements IAppRepository {

    private IDateTimeService $dateTimeService;

    public function __construct(
        IBackend $backend
        , IDateTimeService $dateTimeService
    ) {
        parent::__construct($backend);
        $this->dateTimeService = $dateTimeService;
    }

    public function getAllApps(): HashTable {
        $map = new HashTable();

        $queryBuilder = $this->getQueryBuilder();
        $queryBuilder->select(
            [
                'app_id'
                , 'enabled'
                , 'create_ts'
                , 'version'
            ]
        )
            ->from('app_config');
        $result = $queryBuilder->execute();
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

    public function getApp(string $id): ?IApp {
        $app          = null;
        $queryBuilder = $this->getQueryBuilder();
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
        $result = $queryBuilder->execute();
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

        }

        return $app;
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
        $this->execute($sql);
        // There is otherwise an exception thrown.
        // Do not know how to handle this better for now
        return true;
    }

}
