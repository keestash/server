<?php

namespace Keestash\Command\ApiLog;

use doganoo\DI\DateTime\IDateTimeService;
use Keestash\Command\KeestashCommand;
use KSP\Command\IKeestashCommand;
use KSP\Core\DTO\Instance\Request\ApiLogInterface;
use KSP\Core\Repository\ApiLog\IApiLogRepository;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class ListAll extends KeestashCommand {

    public function __construct(
        private readonly IApiLogRepository $apiLogRepository,
        private readonly IDateTimeService  $dateTimeService
    ) {
        parent::__construct();
    }

    #[\Override]
    protected function configure(): void {
        $this->setName("apiLog:list")
            ->setDescription("lists apilog");
    }

    #[\Override]
    protected function execute(InputInterface $input, OutputInterface $output): int {
        $userLogs = $this->apiLogRepository->getAll();

        $table = new Table($output);
        $table->setHeaders(['id', 'request id', 'start', 'end', 'data', 'create ts']);

        /** @var ApiLogInterface $log */
        foreach ($userLogs as $log) {

            $table->addRow(
                [
                    $log->getId(),
                    $log->getRequestId(),
                    $this->dateTimeService->toYMDHIS($log->getStart()),
                    $this->dateTimeService->toYMDHIS($log->getEnd()),
                    $log->getData(),
                    $this->dateTimeService->toYMDHIS($log->getCreateTs()),
                ]
            );
            $table->addRow(new TableSeparator());
        }
        $table->render();

        return IKeestashCommand::RETURN_CODE_RAN_SUCCESSFUL;
    }

}