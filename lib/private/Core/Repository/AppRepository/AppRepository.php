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

use doganoo\PHPAlgorithms\Datastructure\Table\HashTable;
use doganoo\PHPUtil\Util\DateTimeUtil;
use Keestash\App\Config\App;
use Keestash\Core\Repository\AbstractRepository;
use KSP\App\Config\IApp;
use KSP\Core\Repository\AppRepository\IAppRepository;
use PDO;

class AppRepository extends AbstractRepository implements IAppRepository {

    public function getAllApps(): HashTable {
        $map = new HashTable();

        $tree = null;
        $sql  = "select 
                        `app_id`
                        , `enabled`
                        , `create_ts`
                        , `version`
                 from `app_config`";

        $statement = parent::prepareStatement($sql);

        if (null === $statement) {
            return $map;
        }
        $statement->execute();

        while ($row = $statement->fetch(PDO::FETCH_BOTH)) {
            $appId    = $row[0];
            $enabled  = $row[1];
            $createTs = $row[2];
            $version  = $row[3];

            $app = new App();
            $app->setId($appId);
            $app->setEnabled($enabled === IApp::ENABLED_TRUE);
            $app->setVersion((int) $version);
            $app->setCreateTs(
                DateTimeUtil::fromMysqlDateTime($createTs)
            );

            $map->put($app->getId(), $app);

        }
        return $map;
    }

    public function getApp(string $id): ?IApp {

        $tree = null;
        $sql  = "select 
                        `app_id`
                        , `enabled`
                        , `create_ts`
                        , `version`
                 from `app_config`
                    where `app_id` = :id;
                 ";

        $statement = parent::prepareStatement($sql);

        if (null === $statement) {
            return $tree;
        }
        $statement->bindParam("id", $id);
        $statement->execute();


        if (0 === $statement->rowCount()) return null;

        $row      = $statement->fetch(PDO::FETCH_BOTH);
        $appId    = $row[0];
        $enabled  = $row[1];
        $createTs = $row[2];
        $version  = $row[3];

        $app = new App();
        $app->setId($appId);
        $app->setEnabled($enabled === IApp::ENABLED_TRUE);
        $app->setVersion((int) $version);
        $app->setCreateTs(
            DateTimeUtil::fromMysqlDateTime($createTs)
        );

        return $app;
    }

    public function replace(IApp $app): bool {
        $sql = "
                replace into `app_config` (
                                          `app_id`
                                          , `enabled`
                                          , `version`
                                          )
                values (
                        :app_id
                        , :enabled
                        , :version
                );
        ";

        $statement = parent::prepareStatement($sql);
        if (null === $statement) return false;

        $appId   = $app->getId();
        $enabled = $app->isEnabled() ? "true" : "false";
        $version = $app->getVersion();

        $statement->bindParam("app_id", $appId);
        $statement->bindParam("enabled", $enabled);
        $statement->bindParam("version", $version);

        return $statement->execute();
    }

}