<?php

namespace Keestash\Command\ApiLog;

use doganoo\DI\DateTime\IDateTimeService;
use Keestash\Command\KeestashCommand;
use KSP\Command\IKeestashCommand;
use KSP\Core\DTO\Instance\Request\IAPIRequest;
use KSP\Core\Repository\ApiLog\IApiLogRepository;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class ListAll extends KeestashCommand {

    public const OPTION_NAME_FORCE = 'force';

    public function __construct(
        private readonly IApiLogRepository $apiLogRepository
    ) {
        parent::__construct();
    }

    protected function configure(): void {
        $this->setName("apiLog:list")
            ->setDescription("lists apilog");
    }

    protected function execute(InputInterface $input, OutputInterface $output): int {
        $userLogs = $this->apiLogRepository->getAll();

        $table = new Table($output);
        $table->setHeaders(['user', 'token ts', 'token name', 'route', 'start', 'end', 'duration']);

        $routeCount = 0;
        /** @var IAPIRequest $log */
        foreach ($userLogs as $log) {
            $log->getToken()->getName();
            $log->getRoute();
            $log->getStart();
            $log->getEnd();

            $table->addRow(
                [
                    $log->getToken()->getUser()->getName(),
                    $log->getToken()->getCreateTs()->format(IDateTimeService::FORMAT_YMD_HIS),
                    $log->getRoute(),
                    $log->getStart()->format(IDateTimeService::FORMAT_YMD_HIS),
                    $log->getEnd()->format(IDateTimeService::FORMAT_YMD_HIS),
                    $log->getDuration()
                ]
            );
            $table->addRow(new TableSeparator());
        }
        $table->render();

        return IKeestashCommand::RETURN_CODE_RAN_SUCCESSFUL;
    }

}