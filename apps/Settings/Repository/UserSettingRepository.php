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

namespace KSA\Settings\Repository;

use Doctrine\DBAL\Exception;
use doganoo\DI\DateTime\IDateTimeService;
use doganoo\PHPAlgorithms\Datastructure\Lists\ArrayList\ArrayList;
use Keestash\Exception\Queue\QueueNotCreatedException;
use KSA\Settings\Entity\Setting;
use KSA\Settings\Entity\UserSetting;
use KSA\Settings\Exception\SettingNotDeletedException;
use KSA\Settings\Exception\SettingNotFoundException;
use KSA\Settings\Exception\SettingsException;
use KSP\Core\Backend\IBackend;
use Psr\Log\LoggerInterface;

class UserSettingRepository {

    public function __construct(
        private readonly IBackend           $backend
        , private readonly IDateTimeService $dateTimeService
        , private readonly LoggerInterface  $logger
    ) {
    }

    public function add(UserSetting $userSetting): void {
        try {
            $queryBuilder = $this->backend->getConnection()->createQueryBuilder();
            $queryBuilder->insert("`user_setting`")
                ->values(
                    [
                        "`key`"         => '?'
                        , "`user_id`"   => '?'
                        , "`value`"     => '?'
                        , "`create_ts`" => '?'
                    ]
                )
                ->setParameter(0, $userSetting->getKey())
                ->setParameter(1, $userSetting->getUser()->getId())
                ->setParameter(2, $userSetting->getValue())
                ->setParameter(3, $this->dateTimeService->toYMDHIS($userSetting->getCreateTs()))
                ->executeStatement();
        } catch (Exception $exception) {
            $this->logger->error('error inserting queue', ['exception' => $exception]);
            throw new QueueNotCreatedException();
        }

    }

    public function remove(string $key): void {
        try {
            $queryBuilder = $this->backend->getConnection()->createQueryBuilder();
            $queryBuilder->delete(
                'setting'
            )
                ->where('`key` = ?')
                ->setParameter(0, $key)
                ->executeStatement();
        } catch (Exception $e) {
            $this->logger->error('not deleted setting', ['key' => $key, 'exception' => $e]);
            throw new SettingNotDeletedException();
        }
    }

    public function get(string $key): Setting {
        try {
            $queryBuilder = $this->backend->getConnection()->createQueryBuilder();
            $queryBuilder->select(
                [
                    's.`key`'
                    , 's.value'
                    , 's.create_ts'
                ]
            )
                ->from('setting', 's')
                ->where('s.`key` = ?')
                ->setParameter(0, $key);
            $result = $queryBuilder->executeQuery();

            $all      = $result->fetchAllAssociative();
            $allCount = count($all);

            if (0 === $allCount) {
                throw new SettingNotFoundException();
            }

            return new Setting(
                $all[0]['key']
                , $all[0]['value']
                , $this->dateTimeService->fromFormat((string) $all[0]['create_ts'])
            );
        } catch (Exception $exception) {
            $this->logger->error('error getting setting', ['exception' => $exception]);
            throw new SettingsException();
        }
    }

    public function getAll(): ArrayList {
        try {
            $list = new ArrayList();

            $queryBuilder = $this->backend->getConnection()->createQueryBuilder();
            $queryBuilder->select(
                [
                    's.`key`'
                    , 's.`value`'
                    , 's.`create_ts`'
                ]
            )
                ->from('setting', 's');

            $result   = $queryBuilder->executeQuery();
            $settings = $result->fetchAllAssociative();

            foreach ($settings as $row) {
                $user = new Setting(
                    $row['key'],
                    $row['value'],
                    $this->dateTimeService->fromString((string) $row['create_ts'])
                );
                $list->add($user);
            }

            return $list;
        } catch (Exception $exception) {
            $this->logger->error('error retrieving all settings', ['exception' => $exception]);
            throw new SettingsException();
        }
    }


}