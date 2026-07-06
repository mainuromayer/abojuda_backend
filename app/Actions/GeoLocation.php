<?php



namespace App\Actions;



use Exception;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;



class GeoLocation

{

    protected $client;

    protected $apiKey;



    public function __construct()

    {

        $this->client = new Client;

        $this->apiKey = env('GOOGLE_MAPS_API_KEY');

    }



    public function getCoordinates($address): array

    {

        if (empty(trim($address))) {

            Log::info('Geolocation skipped: Empty address provided');

            return ['latitude' => null, 'longitude' => null];

        }



        try {

            $response = $this->client->get('https://maps.googleapis.com/maps/api/geocode/json', [

                'query' => [

                    'address' => $address,

                    'key' => $this->apiKey,

                ],

            ]);



            $data = json_decode($response->getBody(), true);



            if ($data['status'] === 'OK') {

                return [

                    'latitude' => $data['results'][0]['geometry']['location']['lat'],

                    'longitude' => $data['results'][0]['geometry']['location']['lng'],

                ];

            }



            Log::error('Geolocation error: ' . ($data['error_message'] ?? 'Unknown error occurred'));

            return ['latitude' => null, 'longitude' => null];

        } catch (Exception $e) {

            Log::error('Geolocation request failed: ' . $e->getMessage());

            return ['latitude' => null, 'longitude' => null];

        }

    }

}