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

namespace Keestash\Command\Keestash;

use Keestash\Command\KeestashCommand;
use Keestash\ThirdParty\nikolaposa\RateLimit\FileRateLimiter;
use KSP\Command\IKeestashCommand;
use KSP\Core\Service\Core\Data\IDataService;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ClearRateLimiterFile extends KeestashCommand {

    public function __construct(private readonly IDataService $dataService) {
        parent::__construct();
    }

    protected function configure(): void {
        $this->setName("keestash:ratelimiter:file:delete");
    }

    protected function execute(InputInterface $input, OutputInterface $output): int {
        $fileName = $this->dataService->getPath() . '/' . FileRateLimiter::FILE_NAME;

        if (true === is_file($fileName)) {
            $unlinked = unlink($fileName);
            $this->writeInfo(
                sprintf('file delete result %s', $unlinked)
                , $output
            );
            return IKeestashCommand::RETURN_CODE_RAN_SUCCESSFUL;
        }
        $this->writeError(
            sprintf('no file found at %s', $fileName)
            , $output
        );
        return IKeestashCommand::RETURN_CODE_RAN_SUCCESSFUL;
    }

}