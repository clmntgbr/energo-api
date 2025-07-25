<?php

namespace App\Service;

use App\Dto\PlaceDetails;
use App\Dto\PlaceSearchNearby;
use App\Entity\Station;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class GooglePlaceService
{
    private const PLACES_SEARCH_NEARBY = '/v1/places:searchNearby';
    private const PLACES_DETAILS = '/v1/places';

    public function __construct(
        private readonly HttpClientInterface $googlePlacesClient,
        private readonly DenormalizerInterface $denormalizer,
    ) {
    }

    public function getPlaceSearchNearby(Station $station): PlaceSearchNearby
    {
        try {
            $response = $this->googlePlacesClient->request('POST', self::PLACES_SEARCH_NEARBY, [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'X-Goog-FieldMask' => $this->getGooglePlaceSearchNearbyFieldMask(),
                ],
                'json' => [
                    'includedTypes' => ['gas_station'],
                    'rankPreference' => 'DISTANCE',
                    'maxResultCount' => 1,
                    'locationRestriction' => [
                        'circle' => [
                            'center' => [
                                'latitude' => $station->getAddress()->getLatitude(),
                                'longitude' => $station->getAddress()->getLongitude(),
                            ],
                            'radius' => 150,
                        ],
                    ],
                ],
            ]);

            return $this->denormalizer->denormalize($response->toArray(), PlaceSearchNearby::class);
        } catch (TransportExceptionInterface $exception) {
            throw new \RuntimeException(message: 'Failed to get place search nearby from Google Places API.', previous: $exception);
        }
    }

    public function getPlaceDetails(string $placeId): PlaceDetails
    {
        try {
            $response = $this->googlePlacesClient->request('GET', self::PLACES_DETAILS."/$placeId", [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'X-Goog-FieldMask' => $this->getGooglePlaceFieldMask(),
                ],
            ]);

            return $this->denormalizer->denormalize($response->toArray(), PlaceDetails::class);
        } catch (TransportExceptionInterface $exception) {
            throw new \RuntimeException(message: 'Failed to get place details from Google Places API.', previous: $exception);
        }
    }

    private function getGooglePlaceSearchNearbyFieldMask()
    {
        return implode(',', [
            'places.id',
        ]);
    }

    private function getGooglePlaceFieldMask()
    {
        return implode(',', [
            'id',
            'displayName.text',
            'internationalPhoneNumber',
            'location.latitude',
            'location.longitude',
            'rating',
            'websiteUri',
            'businessStatus',
            'userRatingCount',
            'googleMapsLinks.directionsUri',
            'googleMapsLinks.placeUri',
            'addressComponents',
        ]);
    }
}
