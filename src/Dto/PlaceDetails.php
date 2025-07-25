<?php

namespace App\Dto;

use Symfony\Component\Serializer\Attribute\SerializedPath;

class PlaceDetails implements \JsonSerializable
{
    public function __construct(
        #[SerializedPath('[id]')]
        public ?string $id = null,
        #[SerializedPath('[displayName][text]')]
        public ?string $displayName = null,
        #[SerializedPath('[internationalPhoneNumber]')]
        public ?string $internationalPhoneNumber = null,
        #[SerializedPath('[location][latitude]')]
        public ?float $latitude = null,
        #[SerializedPath('[location][longitude]')]
        public ?float $longitude = null,
        #[SerializedPath('[rating]')]
        public ?float $rating = null,
        #[SerializedPath('[websiteUri]')]
        public ?string $websiteUri = null,
        #[SerializedPath('[businessStatus]')]
        public ?string $businessStatus = null,
        #[SerializedPath('[userRatingCount]')]
        public ?int $userRatingCount = null,
        #[SerializedPath('[googleMapsLinks][directionsUri]')]
        public ?string $googleMapsDirectionsUri = null,
        #[SerializedPath('[googleMapsLinks][placeUri]')]
        public ?string $googleMapsPlaceUri = null,
        #[SerializedPath('[addressComponents]')]
        public ?array $addressComponents = null,
    ) {
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'displayName' => $this->displayName,
            'internationalPhoneNumber' => $this->internationalPhoneNumber,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'rating' => $this->rating,
            'websiteUri' => $this->websiteUri,
            'businessStatus' => $this->businessStatus,
            'userRatingCount' => $this->userRatingCount,
            'googleMapsDirectionsUri' => $this->googleMapsDirectionsUri,
            'googleMapsPlaceUri' => $this->googleMapsPlaceUri,
            'addressComponents' => $this->addressComponents,
        ];
    }
}
