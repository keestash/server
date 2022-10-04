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

namespace KSA\GeneralApi\Command\Info;

use Keestash\Command\KeestashCommand;
use Keestash\ConfigProvider;
use KSP\Command\IKeestashCommand;
use Laminas\Config\Config;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class Routes extends KeestashCommand {

    public const OPTION_NAME_NO_API = 'no-api';
    public const OPTION_NAME_NO_WEB = 'no-web';

    private Config $config;

    public function __construct(
        Config $config
    ) {
        parent::__construct();
        $this->config = $config;
    }

    protected function configure(): void {
        $this->setName('general-api:routes:list')
            ->setDescription('lists all routes and their corresponding info')
            ->addOption(
                Routes::OPTION_NAME_NO_API
                , 'a'
                , InputOption::VALUE_NONE
                , 'exclude api routes'
            )
            ->addOption(Routes::OPTION_NAME_NO_WEB
                , 'w'
                , InputOption::VALUE_NONE
                , 'exclude web routes'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int {

        $routes    = [];
        $webRoutes = [];
        $apiRoutes = [];
        if (false === $input->getOption(Routes::OPTION_NAME_NO_WEB)) {
            $webRoutes = $this->config->get(ConfigProvider::WEB_ROUTER)->toArray();
        }
        if (false === $input->getOption(Routes::OPTION_NAME_NO_API)) {
            $apiRoutes = $this->config->get(ConfigProvider::API_ROUTER)->toArray();
        }

        $routes = array_merge($webRoutes, $apiRoutes);

        if (0 === count($routes)) {
            $this->writeInfo('no routes found. Maybe you excluded everyhting? Please check your options', $output);
            return IKeestashCommand::RETURN_CODE_RAN_SUCCESSFUL;
        }

        $table = new Table($output);
        $table->setHeaders(['path', 'middleware', 'method', 'name']);

        foreach ($routes[ConfigProvider::ROUTES] as $route) {
            $data = $route;

            if (is_array($data['middleware'])) {
                $data['middleware'] = json_encode($data['middleware']);
            }
            if (is_array($data['method'])) {
                $data['method'] = json_encode($data['method']);
            }
            $table->addRow($data);
            $table->addRow(new TableSeparator());
        }

        $table->render();
        return IKeestashCommand::RETURN_CODE_RAN_SUCCESSFUL;
    }

}