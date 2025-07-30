<?php

namespace App\Command;

use App\Application\Command\CreateOrUpdateGasStation;
use App\Dto\MessageBus;
use App\Dto\OpenDataStation;
use App\Service\MessageBusService;
use App\Service\OpenDataService;
use App\Service\XmlToDtoTransformer;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Messenger\Bridge\Amqp\Transport\AmqpStamp;

#[AsCommand(
    name: 'app:update:gas-energy',
    description: 'Update the gas energy prices',
)]
class UpdateGasEnergyCommand extends Command
{
    public const ZIP_NAME = OpenDataService::DIRECTORY.'/opendata.zip';
    public const XML_NAME = OpenDataService::DIRECTORY.'/opendata.xml';

    public function __construct(
        private readonly OpenDataService $openDataService,
        private readonly XmlToDtoTransformer $xmlToDtoTransformer,
        private readonly MessageBusService $bus,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // Clean up
        $this->openDataService->remove(self::XML_NAME);
        $this->openDataService->remove(self::ZIP_NAME);

        $this->openDataService->get(self::ZIP_NAME, OpenDataService::URL_INSTANTANEOUS);
        $this->openDataService->unzip(self::ZIP_NAME, self::XML_NAME);
        $this->openDataService->remove(self::ZIP_NAME);

        $stations = $this->xmlToDtoTransformer->transformXmlFile(self::XML_NAME);

        array_map(function (OpenDataStation $station) {
            $this->bus->dispatch(
                messages: [
                    new MessageBus(
                        command: new CreateOrUpdateGasStation($station),
                        stamp: new AmqpStamp('async-high'),
                    ),
                ],
            );
        }, $stations);

        $this->openDataService->remove(self::XML_NAME);

        return Command::SUCCESS;
    }
}
