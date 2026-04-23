<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class DistanceService
{
    private const BORDEAUX_COORDS = [44.837935, -0.578891];

    public function __construct(
        private HttpClientInterface $httpClient,
        private string $apiKey
    ) {}

    public function getDistanceFromBordeaux(string $address, string $city, string $zipcode): ?float
    {

        $geocodeResponse = $this->httpClient->request('GET', 'https://api.openrouteservice.org/geocode/search/structured', [
            'headers' => ['Authorization' => $this->apiKey],
            'query' => [
                'address'    => $address,
                'postalcode' => $zipcode,
                'locality'   => $city,
                'country'    => 'FRA',
                'size'       => 1,
            ]
        ]);


        $geocodeData = $geocodeResponse->toArray();

        if (empty($geocodeData['features'])) {
            return null;
        }

        $coords = $geocodeData['features'][0]['geometry']['coordinates'];
        $destLon = $coords[0];
        $destLat = $coords[1];

        $routeResponse = $this->httpClient->request('POST', 'https://api.openrouteservice.org/v2/directions/driving-car', [
            'headers' => [
                'Authorization' => $this->apiKey,
                'Content-Type' => 'application/json; charset=utf-8',
                'Accept' => 'application/json, application/geo+json, application/gpx+xml, img/png; charset=utf-8',
            ],

            'json' => [
                'coordinates' => [
                    [self::BORDEAUX_COORDS[1], self::BORDEAUX_COORDS[0]],
                    [$destLon, $destLat],
                ]
            ]
        ]);

        $routeData = $routeResponse->toArray();

        if (empty($routeData['routes'])) {
            return null;
        }

        $distanceMeters = $routeData['routes'][0]['summary']['distance'];
        return round($distanceMeters / 1000, 2);
    }
}
