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

namespace Keestash\Command\App;

use Keestash\Command\KeestashCommand;
use KSP\Command\IKeestashCommand;
use KSP\Core\DTO\App\IApp;
use KSP\Core\Service\App\ILoaderService;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ListAll extends KeestashCommand {

    public function __construct(private readonly ILoaderService $loaderService) {
        parent::__construct();
    }

    protected function configure(): void {
        $this->setName("app:list")
            ->setDescription("lists all installed apps");
    }

    protected function execute(InputInterface $input, OutputInterface $output): int {
        $apps      = $this->loaderService->getApps();
        $tableRows = [];
        /** @var IApp $app */
        foreach ($apps->toArray() as $app) {
            $tableRows[] = [
                'id'        => $app->getId()
                , 'name'    => $app->getName()
                , 'version' => $app->getVersion()
                , 'order'   => $app->getOrder()
            ];
        }

        usort(
            $tableRows
            , static function (array $firstApp, array $secondApp): int {
            return $firstApp['order'] - $secondApp['order'];
        }
        );
        $table = new Table($output);
        $table
            ->setHeaders(['ID', 'Name', 'Version', 'Order'])
            ->setRows($tableRows);
        $table->render();
        return IKeestashCommand::RETURN_CODE_RAN_SUCCESSFUL;
    }

}