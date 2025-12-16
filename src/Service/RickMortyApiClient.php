<?php

namespace App\Service;

use App\Exception\RickMortyApiException;
use App\Exception\ResourceNotFoundException;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;

class RickMortyApiClient
{
    private const BASE_URL = 'https://rickandmortyapi.com/api';

    public function __construct(
        private readonly HttpClientInterface $httpClient
    ) {}

    /**
     * Get all characters with pagination
     */
    public function getCharacters(int $page = 1, array $filters = []): array
    {
        return $this->makeRequest('/character', $page, $filters);
    }

    /**
     * Get a single character by ID
     */
    public function getCharacter(int $id): array
    {
        return $this->makeRequest("/character/{$id}");
    }

    /**
     * Get multiple characters by IDs
     */
    public function getMultipleCharacters(array $ids): array
    {
        $idsString = implode(',', $ids);
        return $this->makeRequest("/character/{$idsString}");
    }

    /**
     * Get all locations with pagination
     */
    public function getLocations(int $page = 1, array $filters = []): array
    {
        return $this->makeRequest('/location', $page, $filters);
    }

    /**
     * Get a single location by ID
     */
    public function getLocation(int $id): array
    {
        return $this->makeRequest("/location/{$id}");
    }

    /**
     * Get multiple locations by IDs
     */
    public function getMultipleLocations(array $ids): array
    {
        $idsString = implode(',', $ids);
        return $this->makeRequest("/location/{$idsString}");
    }

    /**
     * Get all episodes with pagination
     */
    public function getEpisodes(int $page = 1, array $filters = []): array
    {
        return $this->makeRequest('/episode', $page, $filters);
    }

    /**
     * Get a single episode by ID
     */
    public function getEpisode(int $id): array
    {
        return $this->makeRequest("/episode/{$id}");
    }

    /**
     * Get multiple episodes by IDs
     */
    public function getMultipleEpisodes(array $ids): array
    {
        $idsString = implode(',', $ids);
        return $this->makeRequest("/episode/{$idsString}");
    }

    /**
     * Make HTTP request to Rick and Morty API
     */
    private function makeRequest(string $endpoint, ?int $page = null, array $filters = []): array
    {
        try {
            $url = self::BASE_URL . $endpoint;
            $queryParams = [];

            if ($page !== null && $page > 1) {
                $queryParams['page'] = $page;
            }

            // Add filters to query params
            $queryParams = array_merge($queryParams, $filters);

            if (!empty($queryParams)) {
                $url .= '?' . http_build_query($queryParams);
            }

            $response = $this->httpClient->request('GET', $url);

            $statusCode = $response->getStatusCode();

            if ($statusCode === 404) {
                throw new ResourceNotFoundException('Resource not found');
            }

            if ($statusCode !== 200) {
                throw new RickMortyApiException(
                    "Rick and Morty API returned status code {$statusCode}"
                );
            }

            return $response->toArray();
        } catch (ExceptionInterface $e) {
            if (str_contains($e->getMessage(), '404')) {
                throw new ResourceNotFoundException('Resource not found', 0, $e);
            }
            throw new RickMortyApiException(
                'Error communicating with Rick and Morty API: ' . $e->getMessage(),
                0,
                $e
            );
        }
    }
}
