<?php

namespace App\Command;

use App\Application\Command\CreateOrUpdateGasStation;
use App\Dto\MessageBus;
use App\Entity\PriceHistory;
use App\Entity\Station;
use App\Entity\Type;
use App\Enum\Currency;
use App\Repository\StationRepository;
use App\Repository\PriceHistoryRepository;
use App\Repository\TypeRepository;
use App\Service\MessageBusService;
use App\Service\OpenDataService;
use App\Service\XmlToDtoTransformer;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\Bridge\Amqp\Transport\AmqpStamp;

#[AsCommand(
    name: 'app:update:gas-energy-historical',
    description: 'Update the energy prices',
)]
class UpdateGasEnergyHistoricalCommand extends Command
{
    public const ZIP_NAME = __DIR__.'/../../public/opendata-historical-%s.zip';
    public const XML_NAME = OpenDataService::DIRECTORY.'/opendata-historical-%s.xml';
    public const SQL_NAME = __DIR__.'/../../public/opendata-historical-%s.sql';

    public function __construct(
        private readonly OpenDataService $openDataService,
        private readonly XmlToDtoTransformer $xmlToDtoTransformer,
        private readonly MessageBusService $bus,
        private readonly StationRepository $stationRepository,
        private readonly TypeRepository $typeRepository,
        private readonly PriceHistoryRepository $priceHistoryRepository,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Update gas energy historical');

        $years = ['2024', '2025'];

        foreach ($years as $year) {
            $zipPath = sprintf(self::ZIP_NAME, $year);
            $extractDir = OpenDataService::DIRECTORY.'/historical-'.$year;
            
            if (is_dir($extractDir)) {
                $this->removeDirectory($extractDir);
            }
            
            if (!is_dir($extractDir)) {
                mkdir($extractDir, 0755, true);
            }

            $zip = new \ZipArchive();
            if ($zip->open($zipPath) !== true) {
                $output->writeln("<error>Failed to open zip file: $zipPath</error>");
                return Command::FAILURE;
            }

            $zip->extractTo($extractDir);
            $zip->close();

            $xmlFiles = glob($extractDir.'/*.xml');

            $io->progressStart(count($xmlFiles));

            // Create SQL file with buffer
            $sqlFile = sprintf(self::SQL_NAME, $year);
            $buffer = [];
            $bufferSize = 1000;
            $totalInserts = 0;

            // Write header
            $header = "-- Insert statements for PriceHistory table\n";
            $header .= "-- Generated on: " . date('Y-m-d H:i:s') . "\n\n";
            file_put_contents($sqlFile, $header);

            foreach ($xmlFiles as $xmlFile) {
                $stations = $this->xmlToDtoTransformer->transformXmlFile($xmlFile);

                foreach ($stations as $gasStation) {
                    $station = $this->stationRepository->findOneBy(['stationId' => $gasStation->id]);
                    if (null === $station) {
                        continue;
                    }

                    foreach ($gasStation->prices as $price) {
                        $type = $this->typeRepository->findOneBy(['typeId' => $price->id]);
                        if (null === $type) {
                            continue;
                        }

                        // Add to buffer
                        $buffer[] = sprintf(
                            "INSERT INTO price_history (id, value, currency, type_id, date, station_id, created_at, updated_at) VALUES ('%s', %.2f, '%s', '%s', '%s', '%s', '%s', '%s');",
                            $this->generateUuid(),
                            $price->value,
                            Currency::EUR->getValue(),
                            $type->getId(),
                            $price->updatedAt->format('Y-m-d'),
                            $station->getId(),
                            date('Y-m-d H:i:s'),
                            date('Y-m-d H:i:s')
                        );

                        // Write buffer to file when it reaches the size limit
                        if (count($buffer) >= $bufferSize) {
                            $this->writeBufferToFile($sqlFile, $buffer);
                            $totalInserts += count($buffer);
                            $buffer = [];
                        }
                    }
                }
                $io->progressAdvance();
            }

            // Write remaining buffer
            if (!empty($buffer)) {
                $this->writeBufferToFile($sqlFile, $buffer);
                $totalInserts += count($buffer);
            }

            $io->progressFinish();
            $io->success("SQL file generated: $sqlFile with $totalInserts inserts");

            $this->removeDirectory($extractDir);
        }

        return Command::SUCCESS;
    }

    private function removeDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir.'/'.$file;
            if (is_dir($path)) {
                $this->removeDirectory($path);
            } else {
                unlink($path);
            }
        }
        rmdir($dir);
    }

    private function writeBufferToFile(string $filename, array $buffer): void
    {
        $content = implode("\n", $buffer) . "\n";
        file_put_contents($filename, $content, FILE_APPEND | LOCK_EX);
    }

    private function generateUuid(): string
    {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }
}
