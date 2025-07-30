<?php

namespace App\Controller\Admin;

use App\Entity\Station;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class StationCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Station::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            FormField::addColumn(12),
            FormField::addPanel('Basic Information')
                ->setIcon('fa fa-info-circle')
                ->setColumns(6),
            ...$this->getBasicFields(),

            FormField::addColumn(6),
            FormField::addPanel('Address')
                ->setIcon('fa fa-map-marker-alt')
                ->setColumns(6),
            ...$this->getAddressFields(),

            FormField::addColumn(6),
            FormField::addPanel('Google Place Information')
                ->setIcon('fa fa-map-marker-alt')
                ->setColumns(6),
            ...$this->getGooglePlaceFields(),
        ];
    }

    private function getBasicFields(): array
    {
        return [
            IdField::new('id')->setDisabled()->hideOnIndex()->setColumns(6),
            DateTimeField::new('createdAt')->setDisabled()->hideOnIndex()->setColumns(6),
            DateTimeField::new('updatedAt')->setDisabled()->hideOnIndex()->setColumns(6),
            TextField::new('stationId')->setColumns(6),
            TextField::new('name')->setColumns(6),
            TextField::new('pop')->hideOnIndex()->setColumns(6)->setDisabled(),
            TextField::new('status')->setColumns(6)->setDisabled(),
            NumberField::new('trust')->setColumns(6)->setDisabled(),
            ArrayField::new('statuses')->hideOnIndex()->setDisabled()->setColumns(6),
        ];
    }

    private function getAddressFields(): array
    {
        return [
            TextField::new('address.street')->hideOnIndex()->setLabel('Street'),
            TextField::new('address.city')->hideOnIndex()->setLabel('City'),
            TextField::new('address.postalCode')->hideOnIndex()->setLabel('Postal Code'),
            TextField::new('address.country')->hideOnIndex()->setLabel('Country'),
            NumberField::new('address.latitude')->hideOnIndex()->setLabel('Latitude'),
            NumberField::new('address.longitude')->hideOnIndex()->setLabel('Longitude'),
        ];
    }

    private function getGooglePlaceFields(): array
    {
        return [
            TextField::new('googlePlace.placeId')->hideOnIndex()->setLabel('Place ID'),
            NumberField::new('googlePlace.rating')->hideOnIndex()->setLabel('Rating'),
            NumberField::new('googlePlace.userRatingCount')->hideOnIndex()->setLabel('User Rating Count'),
            TextField::new('googlePlace.internationalPhoneNumber')->hideOnIndex()->setLabel('International Phone Number'),
            TextField::new('googlePlace.businessStatus')->hideOnIndex()->setLabel('Business Status'),
            TextField::new('googlePlace.websiteUri')->hideOnIndex()->setLabel('Website URI'),
            TextField::new('googlePlace.googleMapsDirectionsUri')->hideOnIndex()->setLabel('Google Maps Directions URI'),
            TextField::new('googlePlace.googleMapsPlaceUri')->hideOnIndex()->setLabel('Google Maps Place URI'),
        ];
    }
}
