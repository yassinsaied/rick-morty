<?php

namespace App\Service;

use App\Exception\RickMortyApiException;
use App\Exception\ResourceNotFoundException;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;

/**
 * Client HTTP pour interagir avec l'API Rick and Morty
 * 
 * Ce service encapsule toutes les requêtes HTTP vers l'API publique Rick and Morty.
 * Il gère la pagination, les filtres, et convertit les erreurs HTTP en exceptions typées.
 */
class RickMortyApiClient
{
    /** URL de base de l'API Rick and Morty */
    private const BASE_URL = 'https://rickandmortyapi.com/api';

    /**
     * @param HttpClientInterface $httpClient Client HTTP Symfony pour effectuer les requêtes
     */
    public function __construct(
        private readonly HttpClientInterface $httpClient
    ) {}

    /**
     * Récupère la liste des personnages avec pagination et filtres optionnels
     * 
     * @param int $page Numéro de la page (par défaut: 1)
     * @param array $filters Filtres optionnels (name, status, species, type, gender)
     * @return array Données de l'API contenant les personnages et les infos de pagination
     * @throws ResourceNotFoundException Si aucun personnage ne correspond aux filtres
     * @throws RickMortyApiException Si l'API est injoignable ou retourne une erreur
     */
    public function getCharacters(int $page = 1, array $filters = []): array
    {
        return $this->makeRequest('/character', $page, $filters);
    }

    /**
     * Récupère un personnage spécifique par son ID
     * 
     * @param int $id Identifiant unique du personnage
     * @return array Données complètes du personnage
     * @throws ResourceNotFoundException Si le personnage n'existe pas
     * @throws RickMortyApiException Si l'API est injoignable ou retourne une erreur
     */
    public function getCharacter(int $id): array
    {
        return $this->makeRequest("/character/{$id}");
    }

    /**
     * Récupère plusieurs personnages en une seule requête
     * 
     * @param array<int> $ids Liste des identifiants des personnages à récupérer
     * @return array Tableau contenant les données de tous les personnages demandés
     * @throws ResourceNotFoundException Si aucun des personnages n'existe
     * @throws RickMortyApiException Si l'API est injoignable ou retourne une erreur
     */
    public function getMultipleCharacters(array $ids): array
    {
        $idsString = implode(',', $ids);
        return $this->makeRequest("/character/{$idsString}");
    }

    /**
     * Récupère la liste des localisations avec pagination et filtres optionnels
     * 
     * @param int $page Numéro de la page (par défaut: 1)
     * @param array $filters Filtres optionnels (name, type, dimension)
     * @return array Données de l'API contenant les localisations et les infos de pagination
     * @throws ResourceNotFoundException Si aucune localisation ne correspond aux filtres
     * @throws RickMortyApiException Si l'API est injoignable ou retourne une erreur
     */
    public function getLocations(int $page = 1, array $filters = []): array
    {
        return $this->makeRequest('/location', $page, $filters);
    }

    /**
     * Récupère une localisation spécifique par son ID
     * 
     * @param int $id Identifiant unique de la localisation
     * @return array Données complètes de la localisation
     * @throws ResourceNotFoundException Si la localisation n'existe pas
     * @throws RickMortyApiException Si l'API est injoignable ou retourne une erreur
     */
    public function getLocation(int $id): array
    {
        return $this->makeRequest("/location/{$id}");
    }

    /**
     * Récupère plusieurs localisations en une seule requête
     * 
     * @param array<int> $ids Liste des identifiants des localisations à récupérer
     * @return array Tableau contenant les données de toutes les localisations demandées
     * @throws ResourceNotFoundException Si aucune des localisations n'existe
     * @throws RickMortyApiException Si l'API est injoignable ou retourne une erreur
     */
    public function getMultipleLocations(array $ids): array
    {
        $idsString = implode(',', $ids);
        return $this->makeRequest("/location/{$idsString}");
    }

    /**
     * Récupère la liste des épisodes avec pagination et filtres optionnels
     * 
     * @param int $page Numéro de la page (par défaut: 1)
     * @param array $filters Filtres optionnels (name, episode)
     * @return array Données de l'API contenant les épisodes et les infos de pagination
     * @throws ResourceNotFoundException Si aucun épisode ne correspond aux filtres
     * @throws RickMortyApiException Si l'API est injoignable ou retourne une erreur
     */
    public function getEpisodes(int $page = 1, array $filters = []): array
    {
        return $this->makeRequest('/episode', $page, $filters);
    }

    /**
     * Récupère un épisode spécifique par son ID
     * 
     * @param int $id Identifiant unique de l'épisode
     * @return array Données complètes de l'épisode
     * @throws ResourceNotFoundException Si l'épisode n'existe pas
     * @throws RickMortyApiException Si l'API est injoignable ou retourne une erreur
     */
    public function getEpisode(int $id): array
    {
        return $this->makeRequest("/episode/{$id}");
    }

    /**
     * Récupère plusieurs épisodes en une seule requête
     * 
     * @param array<int> $ids Liste des identifiants des épisodes à récupérer
     * @return array Tableau contenant les données de tous les épisodes demandés
     * @throws ResourceNotFoundException Si aucun des épisodes n'existe
     * @throws RickMortyApiException Si l'API est injoignable ou retourne une erreur
     */
    public function getMultipleEpisodes(array $ids): array
    {
        $idsString = implode(',', $ids);
        return $this->makeRequest("/episode/{$idsString}");
    }

    /**
     * Effectue une requête HTTP vers l'API Rick and Morty
     * 
     * Méthode privée centralisée pour toutes les requêtes vers l'API.
     * Gère la construction de l'URL, les paramètres de pagination, les filtres,
     * et convertit les erreurs HTTP en exceptions typées.
     * 
     * @param string $endpoint Point de terminaison de l'API (ex: '/character/1')
     * @param int|null $page Numéro de page optionnel pour la pagination
     * @param array $filters Filtres optionnels à ajouter à la requête
     * @return array Réponse JSON décodée de l'API
     * @throws ResourceNotFoundException Si la ressource n'existe pas (404)
     * @throws RickMortyApiException Si l'API retourne une erreur ou est injoignable
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
