<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class TmdbService
{
    private string $apiKey = '21435e82612eebc971d2233740115e4f';
    private string $baseUrl = 'https://api.themoviedb.org/3';
    public const IMG_URL = 'https://image.tmdb.org/t/p/w500';

    public function __construct(private HttpClientInterface $httpClient)
    {
    }

    public function search(string $query): array
    {
        $response = $this->httpClient->request('GET', $this->baseUrl . '/search/movie', [
            'query' => [
                'api_key' => $this->apiKey,
                'query' => $query,
                'language' => 'fr-FR',
            ]
        ]);

        return $response->toArray()['results'] ?? [];
    }

    public function getById(int $tmdbId): array
    {
        $response = $this->httpClient->request('GET', $this->baseUrl . '/movie/' . $tmdbId, [
            'query' => [
                'api_key' => $this->apiKey,
                'language' => 'fr-FR',
            ]
        ]);

        return $response->toArray();
    }
}