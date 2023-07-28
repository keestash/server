<?php
declare(strict_types=1);
/**
 * Keestash
 *
 * Copyright (C) <2022> <Dogan Ucar>
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

namespace Keestash\Command\Event;

use Keestash\Command\KeestashCommand;
use Keestash\ConfigProvider;
use Laminas\Config\Config;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ListEvents extends KeestashCommand {

    private Config $config;

    public function __construct(Config $config) {
        parent::__construct();
        $this->config = $config;
    }

    protected function configure(): void {
        $this->setName("event:list")
            ->setDescription("lists all events and their listener");
    }

    protected function execute(InputInterface $input, OutputInterface $output): int {
        $tableRows = [];

        foreach ($this->config->get(ConfigProvider::EVENTS)->toArray() as $event => $listener) {
            $tableRows[] = [
                'event'      => $event
                , 'listener' => json_encode($listener, JSON_PRETTY_PRINT)
            ];
        }
        $table = new Table($output);
        $table
            ->setHeaders(['Event', 'ScheduleUserStateEventListenerListener'])
            ->setRows($tableRows);
        $table->render();
        return 0;
    }

}