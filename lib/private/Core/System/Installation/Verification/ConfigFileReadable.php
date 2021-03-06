<?php /** @noinspection ALL */
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

namespace Keestash\Core\System\Installation\Verification;

use doganoo\PHPUtil\FileSystem\DirHandler;
use Keestash;
use Laminas\Config\Config;

/**
 * Class ConfigFileReadable
 * @package Keestash\Core\System\Installation\Verification
 * @author  Dogan Ucar <dogan@dogan-ucar.de>
 */
class ConfigFileReadable extends AbstractVerification {

    private const KEYS = [
        "show_errors"
        , "debug"
        , "db_host"
        , "db_user"
        , "db_password"
        , "db_name"
        , "db_port"
        , "db_charset"
        , "log_requests"
        , "email_smtp_host"
        , "email_user"
        , "email_password"
    ];

    private const FILES = [
        "config.php"
        , "config.sample.php"
    ];

    private Config $config;

    public function __construct(Config $config) {
        $this->config = $config;
    }

    public function hasProperty(): bool {
        $configRoot = (string) $this->config->get(Keestash\ConfigProvider::CONFIG_PATH);
        $dirHandler = new DirHandler($configRoot);

        if (false === $this->hasFiles($dirHandler)) return false;
        if (false === $this->configMatches($dirHandler)) return false;
        if (false === $this->configFileProperties($dirHandler)) return false;

        return true;

    }

    private function hasFiles(DirHandler $dirHandler): bool {
        $hasFiles = true;

        foreach (ConfigFileReadable::FILES as $file) {
            if (false === $dirHandler->hasFile("config.php")) {
                parent::addMessage(
                    "missing_file"
                    , "{$dirHandler->getPath()} does not contain $file"
                );
                $hasFiles = false;
            }
        }

        return $hasFiles;
    }

    private function configMatches(DirHandler $dirHandler): bool {
        $config        = $dirHandler->findFile("config.php");
        $configSample  = $dirHandler->findFile("config.sample.php");
        $configMatches = true;

        if (null === $config) {
            parent::addMessage(
                "config_match"
                , "config file missing"
            );
            $configMatches = false;
        }

        if (null === $configSample) {
            parent::addMessage(
                "config_match"
                , "config sample file missing"
            );
            $configMatches = false;
        }

        $CONFIG = [];
        /** @phpstan-ignore-next-line */
        include $config->getPath();

        /** @phpstan-ignore-next-line */
        if (false === is_array($CONFIG)) {
            $CONFIG = [];
        }

        $conf      = $CONFIG;
        $confCount = count($conf);
        /** @phpstan-ignore-next-line */
        include $configSample->getPath();
        $confSample      = $CONFIG;
        $confSampleCount = count($confSample);

        if (0 === $confCount) {
            parent::addMessage(
                "config_match"
                , "config array missing"
            );
            $configMatches = false;
            $conf          = [];
        }

        if (0 === $confSampleCount) {
            parent::addMessage(
                "config_match"
                , "config sample array missing"
            );
            $configMatches = false;
        }

        $difference = array_diff(
            array_keys($conf)
            , array_keys($confSample)
        );

        $configCount = count($difference);

        if ($configCount > 0) {
            parent::addMessage(
                "config_match"
                , "there are differences in configs " . json_encode($difference)
            );
            $configMatches = false;
        }
        return $configMatches;
    }

    private function configFileProperties(DirHandler $dirHandler): bool {
        $config    = $dirHandler->findFile("config.php");
        $installed = true;

        if (null === $config) return false;

        include $config->getPath();

        $messages = [];
        foreach (ConfigFileReadable::KEYS as $key) {
            $val = $CONFIG[$key] ?? null;

            if (null === $val || "" === $val) {
                parent::addMessage($key, "$val is not set or is null");
                $installed = false;
            }
        }


        return $installed;
    }

}
