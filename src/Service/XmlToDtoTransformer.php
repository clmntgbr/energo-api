<?php

namespace App\Service;

use App\Dto\OpenDataPrice;
use App\Dto\OpenDataStation;
use App\Dto\Price;
use App\Dto\Station;

class XmlTransformException extends \Exception
{
}

class XmlToDtoTransformer
{
    /**
     * Transform an XML file into an array of Station DTOs.
     */
    public function transformXmlFile(string $xmlFilePath): array
    {
        if (!file_exists($xmlFilePath)) {
            throw new XmlTransformException("XML file does not exist: $xmlFilePath");
        }
        $xmlContent = file_get_contents($xmlFilePath);
        if (false === $xmlContent) {
            throw new XmlTransformException("Unable to read XML file: $xmlFilePath");
        }

        return $this->transformXmlString($xmlContent);
    }

    /**
     * Transform an XML string into an array of Station DTOs.
     */
    private function transformXmlString(string $xmlContent): array
    {
        $dom = new \DOMDocument();
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        if (!@$dom->loadXML($xmlContent)) {
            throw new XmlTransformException('Error while parsing XML');
        }
        $xpath = new \DOMXPath($dom);
        $stations = [];
        foreach ($xpath->query('//pdv') as $pdvNode) {
            $stations[] = $this->parseStation($pdvNode);
        }

        return $stations;
    }

    /**
     * Parse a <pdv> node into a Station DTO.
     */
    private function parseStation(\DOMElement $pdvNode): OpenDataStation
    {
        return new OpenDataStation(
            id: $pdvNode->getAttribute('id'),
            latitude: (float) $pdvNode->getAttribute('latitude') / 100000,
            longitude: (float) $pdvNode->getAttribute('longitude') / 100000,
            postalCode: $pdvNode->getAttribute('cp'),
            pop: $pdvNode->getAttribute('pop'),
            address: $this->getSingleNodeValue('adresse', $pdvNode),
            city: $this->getSingleNodeValue('ville', $pdvNode),
            services: $this->parseServices($pdvNode),
            prices: $this->parsePrices($pdvNode),
        );
    }

    /**
     * Get the value of a single child node by tag name.
     */
    private function getSingleNodeValue(string $tag, \DOMElement $context): string
    {
        $node = $context->getElementsByTagName($tag)->item(0);

        return $node ? $node->nodeValue : '';
    }

    /**
     * Parse all <service> nodes into an array of strings.
     */
    private function parseServices(\DOMElement $pdvNode): array
    {
        $services = [];
        $serviceNodes = $pdvNode->getElementsByTagName('service');
        foreach ($serviceNodes as $serviceNode) {
            $services[] = $serviceNode->nodeValue;
        }

        return $services;
    }

    /**
     * Parse all <prix> nodes into an array of Price DTOs.
     */
    private function parsePrices(\DOMElement $pdvNode): array
    {
        $prices = [];
        $priceNodes = $pdvNode->getElementsByTagName('prix');
        foreach ($priceNodes as $priceNode) {
            $prices[] = new OpenDataPrice(
                name: $priceNode->getAttribute('nom'),
                id: $priceNode->getAttribute('id'),
                updatedAt: new \DateTime($priceNode->getAttribute('maj')),
                value: (float) $priceNode->getAttribute('valeur')
            );
        }

        return $prices;
    }
}
