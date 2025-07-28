<?php

namespace App\Service;

use App\Command\UpdateEnergyCommand;

class OpenDataService
{
    public const URL_INSTANTANEOUS = 'https://donnees.roulez-eco.fr/opendata/instantane';
    public const URL_HISTORICAL = 'https://donnees.roulez-eco.fr/opendata/annee/';
    public const DIRECTORY = __DIR__.'/../../public/data';

    public function __construct()
    {
    }

    public function get(string $zipFilePath, string $url)
    {
        $fileData = file_get_contents($url);
        if (false === $fileData) {
            throw new \RuntimeException('Failed to download the zip file.');
        }

        $result = file_put_contents($zipFilePath, $fileData);
        if (false === $result) {
            throw new \RuntimeException('Failed to save the zip file.');
        }

        return $zipFilePath;
    }

    public function unzip(string $zipFilePath, string $newFilePath)
    {
        $zip = new \ZipArchive();
        if (true === $zip->open($zipFilePath)) {
            $zip->extractTo(self::DIRECTORY);
            $firstFileName = $zip->getNameIndex(0);
            $zip->close();

            $extractedPath = self::DIRECTORY.'/'.$firstFileName;

            if (file_exists($extractedPath)) {
                rename($extractedPath, $newFilePath);
            }
        } else {
            throw new \RuntimeException('Failed to open the zip file.');
        }
    }

    public function remove(string $filePath)
    {
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }
}
