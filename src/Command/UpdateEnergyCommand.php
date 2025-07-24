<?php

namespace App\Command;

use App\Service\OpenDataService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:update:energy',
    description: 'Update the energy prices',
)]
class UpdateEnergyCommand extends Command
{
    private const FILE_NAME = 'opendata.zip';
    private const NEW_FILE_NAME = 'opendata.xml';

    public function __construct(
        private readonly OpenDataService $openDataService,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $this->openDataService->get(self::FILE_NAME);
        $this->openDataService->unzip(self::FILE_NAME, self::NEW_FILE_NAME);
        $this->openDataService->remove(self::FILE_NAME);

        $io->success('Energy prices updated');

        return Command::SUCCESS;
    }
}
