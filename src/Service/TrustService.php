<?php

namespace App\Service;

use App\Entity\Station;

class TrustService
{
    public function getTrust(Station $station): ?float
    {
        $address = $station->getAddress();
        $googleComponents = $station->getGooglePlace()->getPlaceDetails()['addressComponents'] ?? [];

        $stationParts = [
            'route'         => $address->getStreet(),
            'locality'      => $address->getCity(),
            'postal_code'   => $address->getPostalCode(),
            'country'       => $address->getCountry(),
        ];

        $googleParts = [];
        foreach ($googleComponents as $component) {
            foreach ($component['types'] as $type) {
                if (in_array($type, ['street_number', 'route', 'locality', 'postal_code', 'country'])) {
                    $googleParts[$type] = $component['longText'];
                }
            }
        }

        $totalScore = 0;
        $totalWeight = 0;
        
        foreach ($stationParts as $key => $value) {
            if (isset($googleParts[$key]) && $value && $googleParts[$key]) {
                $stationValue = mb_strtolower(trim($value));
                $googleValue = mb_strtolower(trim($googleParts[$key]));
                
                $weight = match($key) {
                    'postal_code' => 0.3,
                    'locality' => 0.3,
                    'route' => 0.25,
                    'country' => 0.15,
                    default => 0.1
                };
                
                $maxLength = max(strlen($stationValue), strlen($googleValue));
                if ($maxLength > 0) {
                    $distance = levenshtein($stationValue, $googleValue);
                    $similarity = 1 - ($distance / $maxLength);
                    
                    $totalScore += $similarity * $weight;
                    $totalWeight += $weight;
                }
            }
        }
        
        $score = $totalWeight > 0 ? $totalScore / $totalWeight : 0.0;
        return $score;
    }
}
