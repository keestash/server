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

namespace Keestash\Command;

use Keestash\ConfigProvider;
use Keestash\Middleware\DeactivatedRouteMiddleware;
use KSP\Command\IKeestashCommand;
use Laminas\Config\Config;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class Routes extends KeestashCommand {

    public const string OPTION_NAME_PATH                = 'path';
    public const string OPTION_NAME_EXCLUDE_DEACTIVATED = 'exclude-deactivated';

    public function __construct(
        private readonly Config $config
    ) {
        parent::__construct();
    }

    protected function configure(): void {
        $this->setName('keestash:list-routes')
            ->setDescription('lists all routes and their corresponding info')
            ->addOption(
                Routes::OPTION_NAME_PATH
                , 'r'
                , InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY
                , 'filters route path'
            )
            ->addOption(
                Routes::OPTION_NAME_EXCLUDE_DEACTIVATED
                , 'e'
                , InputOption::VALUE_NONE
                , sprintf(
                    'excludes all routes going through %s middleware',
                    DeactivatedRouteMiddleware::class
                )
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int {

        $paths              = (array) $input->getOption(Routes::OPTION_NAME_PATH);
        $excludeDeactivated = (bool) $input->getOption(Routes::OPTION_NAME_EXCLUDE_DEACTIVATED);
        $routes             = $this->config->get(ConfigProvider::API_ROUTER)->toArray();
        if (0 === count($routes)) {
            $this->writeInfo('no routes found. Please check your options', $output);
            return IKeestashCommand::RETURN_CODE_RAN_SUCCESSFUL;
        }

        $table = new Table($output);
        $table->setHeaders(['path', 'middleware', 'method', 'name']);

        $routeCount = 0;
        foreach ($routes[ConfigProvider::ROUTES] as $route) {
            $data = $route;

            if (false === $this->isFiltered($data['path'], $paths)) {
                continue;
            }

            $middleware = $data['middleware'];
            if (false === is_array($middleware)) {
                $middleware = [$middleware];
            }

            if (true === $this->excludesDeactivated($excludeDeactivated, $middleware)) {
                continue;
            }

            $data['middleware'] = json_encode($data['middleware']);
            if (is_array($data['method'])) {
                $data['method'] = json_encode($data['method']);
            }
            $table->addRow($data);
            $table->addRow(new TableSeparator());
            $routeCount++;
        }

        $table->render();
        $this->writeInfo(
            sprintf('number of endpoints: %s', $routeCount)
            , $output
        );
        return IKeestashCommand::RETURN_CODE_RAN_SUCCESSFUL;
    }

    private function excludesDeactivated(bool $exclude, array $middleware): bool {
        if (false === $exclude) {
            return false;
        }
        return in_array(DeactivatedRouteMiddleware::class, $middleware, true);
    }

    private function isFiltered(string $path, array $filterPaths): bool {
        if (0 === count($filterPaths)) {
            return true;
        }

        foreach ($filterPaths as $filterPath) {
            if (true === str_contains($path, $filterPath)) {
                return true;
            }
        }
        return false;
    }

}