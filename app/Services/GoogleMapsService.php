<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GoogleMapsService
{

    protected $apiKey = 'AIzaSyD1mHrxMd6CLFNjJoE4jinQg-LkVOmowbg';

    public function getCoordinatesFromAddress($address)
    {

        $url = "https://maps.googleapis.com/maps/api/geocode/json?address=" . urlencode($address) . "&key=" . $this->apiKey;

        $response = Http::get($url);
        $data = $response->json();

        Log::info('Google Maps API Response: ', $data);

        if ($data['status'] === 'OK') {
            return [
                'latitude' => $data['results'][0]['geometry']['location']['lat'],
                'longitude' => $data['results'][0]['geometry']['location']['lng'],
            ];
        } else {
            Log::error('Google Maps API Error: ', $data);
        }

        return null;
    }
}
