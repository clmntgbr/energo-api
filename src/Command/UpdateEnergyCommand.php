<?php

namespace App\Command;

use App\Application\Command\CreateOrUpdateGasStation;
use App\Service\OpenDataService;
use App\Service\XmlToDtoTransformer;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Messenger\Bridge\Amqp\Transport\AmqpStamp;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsCommand(
    name: 'app:update:energy',
    description: 'Update the energy prices',
)]
class UpdateEnergyCommand extends Command
{
    private const DIRECTORY = __DIR__.'/../../public/data';
    private const ZIP_NAME = self::DIRECTORY.'/opendata.zip';
    private const XML_NAME = self::DIRECTORY.'/opendata.xml';

    public function __construct(
        private readonly OpenDataService $openDataService,
        private readonly XmlToDtoTransformer $xmlToDtoTransformer,
        private readonly MessageBusInterface $bus,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        //Clean up
        $this->openDataService->remove(self::XML_NAME);
        $this->openDataService->remove(self::ZIP_NAME);
        
        $this->openDataService->get(self::ZIP_NAME);
        $this->openDataService->unzip(self::ZIP_NAME, self::XML_NAME);
        $this->openDataService->remove(self::ZIP_NAME);

        $stations = $this->xmlToDtoTransformer->transformXmlFile(self::XML_NAME);

        $max = 5;
        foreach ($stations as $station) {
            $this->bus->dispatch(new CreateOrUpdateGasStation($station), [new AmqpStamp('async-high')]);
            --$max;
            if (0 === $max) {
                break;
            }
        }

        $this->openDataService->remove(self::XML_NAME);

        return Command::SUCCESS;
    }
}
