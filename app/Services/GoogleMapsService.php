<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GoogleMapsService
{
    private const AUTOCOMPLETE_URL = 'https://maps.googleapis.com/maps/api/place/autocomplete/json';
    private const DETAILS_URL = 'https://maps.googleapis.com/maps/api/place/details/json';
    private const GEOCODE_URL = 'https://maps.googleapis.com/maps/api/geocode/json';

    public function apiKey(): ?string
    {
        $key = env('GOOGLE_MAPS_API_KEY') ?: env('GOOGLE_MAP_KEY');
        $key = $key ? trim((string) $key) : '';

        // Same key the website Places Autocomplete already uses.
        if ($key === '') {
            $key = 'AIzaSyBUMK9qFdsbuuuTMiaPHCJok4Rro91yvaE';
        }

        return $key !== '' ? $key : null;
    }

    public function autocomplete(string $query, array $options = []): array
    {
        $query = trim($query);
        if ($query === '') {
            return [];
        }

        $key = $this->apiKey();
        if (!$key) {
            throw new \RuntimeException('Google Maps API key is not configured.');
        }

        $params = [
            'input' => $query,
            'key' => $key,
            'types' => $options['types'] ?? 'geocode',
            'components' => $options['components'] ?? 'country:gb',
            'language' => $options['language'] ?? 'en',
        ];

        if (!empty($options['session_token'])) {
            $params['sessiontoken'] = $options['session_token'];
        }

        $response = Http::timeout(8)->get(self::AUTOCOMPLETE_URL, $params);
        $data = $response->json() ?: [];

        if (($data['status'] ?? '') === 'ZERO_RESULTS') {
            return [];
        }

        if (!$response->successful() || !in_array($data['status'] ?? '', ['OK', 'ZERO_RESULTS'], true)) {
            Log::warning('Google Places autocomplete failed', [
                'status' => $data['status'] ?? null,
                'error' => $data['error_message'] ?? $response->body(),
            ]);
            throw new \RuntimeException($data['error_message'] ?? 'Could not load address suggestions.');
        }

        $rows = [];
        foreach (($data['predictions'] ?? []) as $row) {
            $rows[] = [
                'place_id' => $row['place_id'] ?? null,
                'description' => $row['description'] ?? '',
                'main_text' => $row['structured_formatting']['main_text'] ?? ($row['description'] ?? ''),
                'secondary_text' => $row['structured_formatting']['secondary_text'] ?? '',
            ];
        }

        return $rows;
    }

    public function placeDetails(string $placeId, array $options = []): array
    {
        $placeId = trim($placeId);
        if ($placeId === '') {
            throw new \RuntimeException('Please select an address from the suggestions.');
        }

        $key = $this->apiKey();
        if (!$key) {
            throw new \RuntimeException('Google Maps API key is not configured.');
        }

        $params = [
            'place_id' => $placeId,
            'key' => $key,
            'fields' => 'place_id,formatted_address,geometry,name,address_component',
            'language' => $options['language'] ?? 'en',
        ];

        if (!empty($options['session_token'])) {
            $params['sessiontoken'] = $options['session_token'];
        }

        $response = Http::timeout(8)->get(self::DETAILS_URL, $params);
        $data = $response->json() ?: [];

        if (!$response->successful() || ($data['status'] ?? '') !== 'OK' || empty($data['result'])) {
            Log::warning('Google Places details failed', [
                'place_id' => $placeId,
                'status' => $data['status'] ?? null,
                'error' => $data['error_message'] ?? $response->body(),
            ]);
            throw new \RuntimeException($data['error_message'] ?? 'Could not load that address. Please pick another suggestion.');
        }

        return $this->formatPlace($data['result']);
    }

    public function geocode(string $address): array
    {
        $address = trim($address);
        if ($address === '') {
            throw new \RuntimeException('Please enter a delivery address.');
        }

        $key = $this->apiKey();
        if (!$key) {
            throw new \RuntimeException('Google Maps API key is not configured.');
        }

        $response = Http::timeout(8)->get(self::GEOCODE_URL, [
            'address' => $address,
            'key' => $key,
            'components' => 'country:GB',
            'language' => 'en',
        ]);
        $data = $response->json() ?: [];

        if (!$response->successful() || ($data['status'] ?? '') !== 'OK' || empty($data['results'][0])) {
            throw new \RuntimeException('Please select a valid address from the suggestions.');
        }

        return $this->formatPlace($data['results'][0]);
    }

    protected function formatPlace(array $result): array
    {
        $location = $result['geometry']['location'] ?? [];
        $lat = isset($location['lat']) ? (float) $location['lat'] : null;
        $lng = isset($location['lng']) ? (float) $location['lng'] : null;

        if ($lat === null || $lng === null) {
            throw new \RuntimeException('Please select a valid address from the suggestions.');
        }

        return [
            'place_id' => $result['place_id'] ?? null,
            'name' => $result['name'] ?? null,
            'formatted_address' => $result['formatted_address'] ?? ($result['name'] ?? ''),
            'latitude' => $lat,
            'longitude' => $lng,
            'lat' => $lat,
            'lng' => $lng,
        ];
    }
}
