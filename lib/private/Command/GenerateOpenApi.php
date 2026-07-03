<?php
declare(strict_types=1);

namespace Keestash\Command;

use KSP\Command\IKeestashCommand;
use OpenApi\Generator;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

class GenerateOpenApi extends KeestashCommand {

    public const string OPTION_FORMAT = 'format';
    public const string OPTION_OUTPUT = 'output';

    #[\Override]
    protected function configure(): void {
        $this->setName('keestash:generate-openapi')
            ->setDescription('Generates an OpenAPI specification from PHP attributes')
            ->addOption(
                self::OPTION_FORMAT,
                'f',
                InputOption::VALUE_OPTIONAL,
                'Output format: yaml or json',
                'yaml'
            )
            ->addOption(
                self::OPTION_OUTPUT,
                'o',
                InputOption::VALUE_OPTIONAL,
                'Write to file instead of stdout'
            );
    }

    #[\Override]
    protected function execute(InputInterface $input, OutputInterface $output): int {
        $format     = (string) $input->getOption(self::OPTION_FORMAT);
        $outputFile = $input->getOption(self::OPTION_OUTPUT);

        $appsFinder = (new Finder())
            ->files()
            ->name('*.php')
            ->in(__DIR__ . '/../../../apps')
            ->exclude('Migration')
            ->exclude('Test')
            ->followLinks();

        $openapi = Generator::scan([$appsFinder, __DIR__ . '/../OpenApi']);

        if (null === $openapi) {
            $output->writeln('<error>No OpenAPI annotations found in scan paths.</error>');
            return IKeestashCommand::RETURN_CODE_NOT_RAN_SUCCESSFUL;
        }

        $content = match ($format) {
            'json'  => $openapi->toJson(),
            default => $openapi->toYaml(),
        };

        if (null !== $outputFile) {
            file_put_contents((string) $outputFile, (string) $content);
            $this->writeInfo(sprintf('OpenAPI spec written to %s', (string) $outputFile), $output);
        } else {
            $output->write((string) $content);
        }

        return IKeestashCommand::RETURN_CODE_RAN_SUCCESSFUL;
    }

}
