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

namespace Keestash\Command\Derivation;

use doganoo\DI\DateTime\IDateTimeService;
use Keestash\Command\KeestashCommand;
use KSP\Command\IKeestashCommand;
use KSP\Core\DTO\Derivation\IDerivation;
use KSP\Core\Repository\Derivation\IDerivationRepository;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DerivationList extends KeestashCommand {

    public function __construct(
        private readonly IDerivationRepository $derivationRepository
        , private readonly IDateTimeService    $dateTimeService
    ) {
        parent::__construct();
    }

    protected function configure(): void {
        $this->setName("derivation:list")
            ->setDescription("lists all derivation entries");
    }

    protected function execute(InputInterface $input, OutputInterface $output): int {
        $derivations = $this->derivationRepository->getAll();
        $tableRows   = [];
        /** @var IDerivation $derivation */
        foreach ($derivations as $derivation) {
            $tableRows[] = [
                $derivation->getId()
                , $this->dateTimeService->toDMYHIS($derivation->getCreateTs())
                , $derivation->getDerived()
                , $derivation->getKeyHolder()->getId()
            ];
        }

        $table = new Table($output);
        $table
            ->setHeaders(['ID', 'CreateTs', 'Derived', 'UserId'])
            ->setRows($tableRows);
        $table->render();
        return IKeestashCommand::RETURN_CODE_RAN_SUCCESSFUL;
    }

}