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

namespace KSA\InstallInstance\Command\Apps;

use Keestash\Command\KeestashCommand;
use Keestash\Exception\App\AppNotFoundException;
use KSP\Command\IKeestashCommand;
use KSP\Core\Repository\AppRepository\IAppRepository;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Uninstall extends KeestashCommand {

    public const ARGUMENT_NAME_APP_ID = 'app_id';

    public function __construct(
        private readonly IAppRepository    $appRepository
        , private readonly LoggerInterface $logger
    ) {
        parent::__construct();
    }

    protected function configure(): void {
        $this->setName("instance:apps:uninstall")
            ->setDescription("uninstalls an app")
            ->addArgument(
                Uninstall::ARGUMENT_NAME_APP_ID
                , InputArgument::REQUIRED
                , 'the app id'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int {
        $appId = (string) $input->getArgument(Uninstall::ARGUMENT_NAME_APP_ID);
        $app   = null;
        try {
            $app = $this->appRepository->getApp($appId);
        } catch (AppNotFoundException $e) {
            $this->writeError(sprintf('no app with id %s found', $appId), $output);
            $this->logger->error('no app found', ['appId' => $appId, 'exception' => $e]);
            return IKeestashCommand::RETURN_CODE_RAN_SUCCESSFUL;
        }

        $this->appRepository->remove($app);
        return IKeestashCommand::RETURN_CODE_RAN_SUCCESSFUL;
    }

}