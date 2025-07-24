<?php

namespace App\Service;

use App\Repository\StationRepository;
use App\Entity\Station;

class OpenDataService
{
    private const URL = 'https://donnees.roulez-eco.fr/opendata/instantane';
    private const DIRECTORY = __DIR__.'/../../public/data';
    
    public function __construct()
    {
    }

    public function get(string $fileName)
    {
        $zipFilePath = self::DIRECTORY . '/' . $fileName;

        $fileData = file_get_contents(self::URL);
        if ($fileData === false) {
            throw new \RuntimeException('Failed to download the zip file.');
        }

        $result = file_put_contents($zipFilePath, $fileData);
        if ($result === false) {
            throw new \RuntimeException('Failed to save the zip file.');
        }

        return $zipFilePath;
    }

    public function unzip(string $fileName, string $newFileName)
    {
        $zipFilePath = self::DIRECTORY . '/' . $fileName;
        $zip = new \ZipArchive();
        if ($zip->open($zipFilePath) === TRUE) {
            $zip->extractTo(self::DIRECTORY);
            $firstFileName = $zip->getNameIndex(0);
            $zip->close();

            $extractedPath = self::DIRECTORY . '/' . $firstFileName;
            $renamedPath = self::DIRECTORY . '/' . $newFileName;
            if (file_exists($extractedPath)) {
                rename($extractedPath, $renamedPath);
            }
        } else {
            throw new \RuntimeException('Failed to open the zip file.');
        }
    }

    public function remove(string $fileName)
    {
        $filePath = self::DIRECTORY . '/' . $fileName;
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }
}
