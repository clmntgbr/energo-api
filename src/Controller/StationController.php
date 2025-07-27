<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class StationController extends AbstractController
{
    #[Route("/stations/geolocation", name: "geolocation")]
    public function geolocation(): JsonResponse
    {
        return new JsonResponse([
            'message' => 'Hello, world!',
        ]);
    }
}