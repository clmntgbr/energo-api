<?php

namespace App\Service;

use App\DTO\StationServiceDTO;
use App\DTO\CarburantDTO;
use DOMDocument;
use DOMXPath;
use Exception;

/**
 * Exception pour les erreurs de transformation XML
 */
class XmlTransformException extends Exception {}

/**
 * Service pour transformer les données XML en DTO
 */
class XmlToDtoTransformer
{
    /**
     * Transforme un fichier XML en tableau de DTOs
     * 
     * @param string $xmlFilePath Chemin vers le fichier XML
     * @return array<StationServiceDTO>
     * @throws XmlTransformException
     */
    public function transformXmlFile(string $xmlFilePath): array
    {
        if (!file_exists($xmlFilePath)) {
            throw new XmlTransformException("Le fichier XML n'existe pas : $xmlFilePath");
        }

        $xmlContent = file_get_contents($xmlFilePath);
        if ($xmlContent === false) {
            throw new XmlTransformException("Impossible de lire le fichier XML : $xmlFilePath");
        }

        return $this->transformXmlString($xmlContent);
    }

    /**
     * Transforme une chaîne XML en tableau de DTOs
     * 
     * @param string $xmlContent Contenu XML
     * @return array<StationServiceDTO>
     * @throws XmlTransformException
     */
    public function transformXmlString(string $xmlContent): array
    {
        try {
            // Désactiver les erreurs libxml pour éviter les warnings
            $useInternalErrors = libxml_use_internal_errors(true);
            
            $dom = new DOMDocument();
            $dom->preserveWhiteSpace = false;
            $dom->formatOutput = true;
            
            if (!$dom->loadXML($xmlContent)) {
                $errors = libxml_get_errors();
                $errorMessages = array_map(fn($error) => trim($error->message), $errors);
                throw new XmlTransformException("Erreur lors du parsing XML : " . implode(', ', $errorMessages));
            }

            // Restaurer la gestion d'erreurs
            libxml_use_internal_errors($useInternalErrors);

            $xpath = new DOMXPath($dom);
            
            // Détecter automatiquement la structure XML
            return $this->detectAndTransform($xpath);
            
        } catch (Exception $e) {
            throw new XmlTransformException("Erreur lors de la transformation : " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Détecte automatiquement la structure XML et transforme
     * 
     * @param DOMXPath $xpath
     * @return array<StationServiceDTO>
     */
    private function detectAndTransform(DOMXPath $xpath): array
    {
        // Patterns courants pour les données de stations-service
        $patterns = [
            '//station',
            '//pdv',
            '//point_de_vente',
            '//station_service',
            '//item',
            '//record',
            '//entry'
        ];

        foreach ($patterns as $pattern) {
            $nodes = $xpath->query($pattern);
            if ($nodes && $nodes->length > 0) {
                return $this->transformNodes($nodes, $xpath);
            }
        }

        // Si aucun pattern n'est trouvé, essayer avec l'élément racine
        $rootNodes = $xpath->query('/*/*');
        if ($rootNodes && $rootNodes->length > 0) {
            return $this->transformNodes($rootNodes, $xpath);
        }

        throw new XmlTransformException("Impossible de détecter la structure XML");
    }

    /**
     * Transforme une liste de nœuds DOM en DTOs
     * 
     * @param \DOMNodeList $nodes
     * @param DOMXPath $xpath
     * @return array<StationServiceDTO>
     */
    private function transformNodes(\DOMNodeList $nodes, DOMXPath $xpath): array
    {
        $dtos = [];

        foreach ($nodes as $node) {
            $data = $this->extractNodeData($node, $xpath);
            $dtos[] = StationServiceDTO::fromArray($data);
        }

        return $dtos;
    }

    /**
     * Extrait les données d'un nœud XML
     * 
     * @param \DOMNode $node
     * @param DOMXPath $xpath
     * @return array
     */
    private function extractNodeData(\DOMNode $node, DOMXPath $xpath): array
    {
        $data = [];

        // Mapping des champs courants
        $fieldMappings = [
            'id' => ['@id', '@identifiant', 'id', 'identifiant', '@cp'],
            'nom' => ['nom', 'name', 'enseigne', 'raison_sociale'],
            'adresse' => ['adresse', 'address', 'rue', 'voie'],
            'ville' => ['ville', 'city', 'commune'],
            'code_postal' => ['code_postal', 'cp', 'postal_code', 'zip'],
            'latitude' => ['latitude', 'lat', '@latitude', '@lat'],
            'longitude' => ['longitude', 'lng', 'lon', '@longitude', '@lng'],
            'marque' => ['marque', 'brand', 'enseigne'],
            'date_maj' => ['maj', 'date_maj', 'updated_at', 'last_update', '@maj'],
            'automate_24h' => ['automate_24h', 'h24', 'ouvert_24h']
        ];

        // Extraire les champs simples
        foreach ($fieldMappings as $dtoField => $xmlFields) {
            $data[$dtoField] = $this->extractFieldValue($node, $xpath, $xmlFields);
        }

        // Extraire les services
        $data['services'] = $this->extractServices($node, $xpath);

        // Extraire les carburants
        $data['carburants'] = $this->extractCarburants($node, $xpath);

        // Extraire les horaires
        $data['horaires'] = $this->extractHoraires($node, $xpath);

        return array_filter($data, fn($value) => $value !== null);
    }

    /**
     * Extrait la valeur d'un champ depuis plusieurs possibilités
     * 
     * @param \DOMNode $node
     * @param DOMXPath $xpath
     * @param array $possibleFields
     * @return mixed
     */
    private function extractFieldValue(\DOMNode $node, DOMXPath $xpath, array $possibleFields)
    {
        foreach ($possibleFields as $field) {
            if (str_starts_with($field, '@')) {
                // Attribut
                $attrName = substr($field, 1);
                if ($node->hasAttribute && $node->hasAttribute($attrName)) {
                    return $node->getAttribute($attrName);
                }
            } else {
                // Élément enfant
                $childNodes = $xpath->query($field, $node);
                if ($childNodes && $childNodes->length > 0) {
                    return trim($childNodes->item(0)->textContent);
                }
            }
        }

        return null;
    }

    /**
     * Extrait la liste des services
     * 
     * @param \DOMNode $node
     * @param DOMXPath $xpath
     * @return array
     */
    private function extractServices(\DOMNode $node, DOMXPath $xpath): array
    {
        $services = [];
        $servicePaths = ['services/service', 'service', 'services/*'];

        foreach ($servicePaths as $path) {
            $serviceNodes = $xpath->query($path, $node);
            if ($serviceNodes && $serviceNodes->length > 0) {
                foreach ($serviceNodes as $serviceNode) {
                    $serviceName = trim($serviceNode->textContent);
                    if (!empty($serviceName)) {
                        $services[] = $serviceName;
                    }
                }
                break;
            }
        }

        return array_unique($services);
    }

    /**
     * Extrait la liste des carburants avec leurs prix
     * 
     * @param \DOMNode $node
     * @param DOMXPath $xpath
     * @return array<CarburantDTO>
     */
    private function extractCarburants(\DOMNode $node, DOMXPath $xpath): array
    {
        $carburants = [];
        $carburantPaths = ['prix/prix', 'carburants/carburant', 'prix/*', 'carburant'];

        foreach ($carburantPaths as $path) {
            $carburantNodes = $xpath->query($path, $node);
            if ($carburantNodes && $carburantNodes->length > 0) {
                foreach ($carburantNodes as $carburantNode) {
                    $carburantData = [
                        'nom' => $carburantNode->getAttribute('nom') ?: $carburantNode->getAttribute('type') ?: $carburantNode->nodeName,
                        'prix' => $carburantNode->getAttribute('valeur') ?: $carburantNode->textContent,
                        'date_maj' => $carburantNode->getAttribute('maj'),
                        'disponible' => true
                    ];

                    // Nettoyer le prix
                    if (isset($carburantData['prix'])) {
                        $carburantData['prix'] = floatval(str_replace(',', '.', $carburantData['prix']));
                    }

                    $carburants[] = CarburantDTO::fromArray($carburantData);
                }
                break;
            }
        }

        return $carburants;
    }

    /**
     * Extrait les horaires d'ouverture
     * 
     * @param \DOMNode $node
     * @param DOMXPath $xpath
     * @return array
     */
    private function extractHoraires(\DOMNode $node, DOMXPath $xpath): array
    {
        $horaires = [];
        $horairePaths = ['horaires/horaire', 'horaire', 'ouverture/*'];

        foreach ($horairePaths as $path) {
            $horaireNodes = $xpath->query($path, $node);
            if ($horaireNodes && $horaireNodes->length > 0) {
                foreach ($horaireNodes as $horaireNode) {
                    $jour = $horaireNode->getAttribute('jour') ?: $horaireNode->getAttribute('day');
                    $ouverture = $horaireNode->getAttribute('ouverture') ?: $horaireNode->getAttribute('open');
                    $fermeture = $horaireNode->getAttribute('fermeture') ?: $horaireNode->getAttribute('close');

                    if ($jour) {
                        $horaires[$jour] = [
                            'ouverture' => $ouverture,
                            'fermeture' => $fermeture
                        ];
                    }
                }
                break;
            }
        }

        return $horaires;
    }

    /**
     * Valide et nettoie les données extraites
     * 
     * @param array $data
     * @return array
     */
    private function validateAndClean(array $data): array
    {
        // Nettoyer les chaînes
        foreach (['nom', 'adresse', 'ville', 'marque'] as $field) {
            if (isset($data[$field])) {
                $data[$field] = trim($data[$field]);
                if (empty($data[$field])) {
                    unset($data[$field]);
                }
            }
        }

        // Valider les coordonnées
        if (isset($data['latitude'])) {
            $lat = floatval($data['latitude']);
            $data['latitude'] = ($lat >= -90 && $lat <= 90) ? $lat : null;
        }

        if (isset($data['longitude'])) {
            $lng = floatval($data['longitude']);
            $data['longitude'] = ($lng >= -180 && $lng <= 180) ? $lng : null;
        }

        // Valider le code postal
        if (isset($data['code_postal'])) {
            $cp = preg_replace('/[^0-9]/', '', $data['code_postal']);
            $data['code_postal'] = (strlen($cp) === 5) ? $cp : null;
        }

        return $data;
    }

    /**
     * Obtient des statistiques sur les données transformées
     * 
     * @param array $dtos
     * @return array
     */
    public function getTransformationStats(array $dtos): array
    {
        $stats = [
            'total_stations' => count($dtos),
            'stations_avec_prix' => 0,
            'stations_avec_coordonnees' => 0,
            'types_carburants' => [],
            'marques' => []
        ];

        foreach ($dtos as $dto) {
            if (!empty($dto->carburants)) {
                $stats['stations_avec_prix']++;
                foreach ($dto->carburants as $carburant) {
                    if ($carburant->nom) {
                        $stats['types_carburants'][$carburant->nom] = 
                            ($stats['types_carburants'][$carburant->nom] ?? 0) + 1;
                    }
                }
            }

            if ($dto->latitude && $dto->longitude) {
                $stats['stations_avec_coordonnees']++;
            }

            if ($dto->marque) {
                $stats['marques'][$dto->marque] = 
                    ($stats['marques'][$dto->marque] ?? 0) + 1;
            }
        }

        return $stats;
    }
}