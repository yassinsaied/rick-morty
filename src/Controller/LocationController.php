<?php

namespace App\Controller;

use App\Service\RickMortyApiClient;
use App\Exception\ResourceNotFoundException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Contrôleur pour la gestion des localisations de Rick and Morty
 * 
 * Expose les endpoints pour récupérer les localisations depuis l'API externe.
 * Tous les endpoints nécessitent une authentification JWT valide.
 */
#[Route('/api/locations', name: 'api_locations_')]
class LocationController
{
    /**
     * @param RickMortyApiClient $apiClient Client pour interagir avec l'API Rick and Morty
     */
    public function __construct(
        private readonly RickMortyApiClient $apiClient
    ) {}

    /**
     * Liste toutes les localisations avec pagination et filtres optionnels
     * 
     * Paramètres de requête supportés:
     * - page: numéro de la page (défaut: 1)
     * - name: filtrer par nom de la localisation
     * - type: filtrer par type de localisation
     * - dimension: filtrer par dimension
     * 
     * @param Request $request Requête HTTP contenant les paramètres de pagination et filtres
     * @return JsonResponse Liste des localisations avec informations de pagination
     */
    #[Route('', name: 'list', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        $page = (int) $request->query->get('page', 1);

        // Extract filters from query parameters
        $filters = array_filter([
            'name' => $request->query->get('name'),
            'type' => $request->query->get('type'),
            'dimension' => $request->query->get('dimension'),
        ]);

        $data = $this->apiClient->getLocations($page, $filters);

        return new JsonResponse($data);
    }

    /**
     * Récupère une localisation spécifique par son ID
     * 
     * @param int $id Identifiant unique de la localisation
     * @return JsonResponse Données complètes de la localisation
     * @throws ResourceNotFoundException Si la localisation n'existe pas
     */
    #[Route('/{id}', name: 'show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(int $id): JsonResponse
    {
        $data = $this->apiClient->getLocation($id);

        return new JsonResponse($data);
    }

    /**
     * Récupère plusieurs localisations par leurs IDs en une seule requête
     * 
     * Exemple d'utilisation: GET /api/locations/multiple?ids=1,2,3
     * 
     * @param Request $request Requête HTTP contenant le paramètre 'ids' (séparés par des virgules)
     * @return JsonResponse Tableau contenant les données de toutes les localisations demandées
     * @throws ResourceNotFoundException Si le paramètre 'ids' est manquant ou vide
     */
    #[Route('/multiple', name: 'multiple', methods: ['GET'])]
    public function multiple(Request $request): JsonResponse
    {
        $idsParam = $request->query->get('ids', '');

        if (empty($idsParam)) {
            throw new ResourceNotFoundException('IDs parameter is required');
        }

        $ids = array_map('intval', explode(',', $idsParam));
        $data = $this->apiClient->getMultipleLocations($ids);

        return new JsonResponse($data);
    }
}
