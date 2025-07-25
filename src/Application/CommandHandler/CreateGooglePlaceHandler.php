<?php

namespace App\Application\CommandHandler;

use App\Application\Command\CreateGooglePlace;
use App\Entity\Address;
use App\Entity\GooglePlace;
use App\Entity\Station;
use App\Repository\StationRepository;
use App\Service\GooglePlaceService;
use App\Service\MessageBusService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class CreateGooglePlaceHandler
{
    public function __construct(
        private readonly StationRepository $stationRepository,
        private readonly GooglePlaceService $googlePlaceService,
        private readonly MessageBusService $bus,
    ) {
    }

    public function __invoke(CreateGooglePlace $message): void
    {
        /** @var ?Station $station */
        $station = $this->stationRepository->findOneBy(['id' => $message->stationId]);

        if (null === $station) {
            return;
        }

        $googlePlace = new GooglePlace();
        $googlePlace->setPlaceId($message->placeDetails->id);
        $googlePlace->setInternationalPhoneNumber($message->placeDetails->internationalPhoneNumber);
        $googlePlace->setRating($message->placeDetails->rating);
        $googlePlace->setUserRatingCount($message->placeDetails->userRatingCount);
        $googlePlace->setBusinessStatus($message->placeDetails->businessStatus);
        $googlePlace->setWebsiteUri($message->placeDetails->websiteUri);
        $googlePlace->setGoogleMapsDirectionsUri($message->placeDetails->googleMapsDirectionsUri);
        $googlePlace->setGoogleMapsPlaceUri($message->placeDetails->googleMapsPlaceUri);
        $googlePlace->setPlaceDetails($message->placeDetails->jsonSerialize());

        $station->setName($message->placeDetails->displayName);
        $station->setGooglePlace($googlePlace);
        $station->setAddress(Address::fromPlaceDetails($message->placeDetails));
        $station->markAsPlaceDetailsSuccess();
        $station->markAsValidationPending();

        $this->stationRepository->save($station);
    }
}
