<?php

namespace App\Controller;

use App\Service\RickMortyApiClient;
use App\Exception\ResourceNotFoundException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Contrôleur pour la gestion des épisodes de Rick and Morty
 * 
 * Expose les endpoints pour récupérer les épisodes depuis l'API externe.
 * Tous les endpoints nécessitent une authentification JWT valide.
 */
#[Route('/api/episodes', name: 'api_episodes_')]
class EpisodeController
{
    /**
     * @param RickMortyApiClient $apiClient Client pour interagir avec l'API Rick and Morty
     */
    public function __construct(
        private readonly RickMortyApiClient $apiClient
    ) {}

    /**
     * Liste tous les épisodes avec pagination et filtres optionnels
     * 
     * Paramètres de requête supportés:
     * - page: numéro de la page (défaut: 1)
     * - name: filtrer par nom de l'épisode
     * - episode: filtrer par code d'épisode (ex: S01E01)
     * 
     * @param Request $request Requête HTTP contenant les paramètres de pagination et filtres
     * @return JsonResponse Liste des épisodes avec informations de pagination
     */
    #[Route('', name: 'list', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        $page = (int) $request->query->get('page', 1);

        // Extract filters from query parameters
        $filters = array_filter([
            'name' => $request->query->get('name'),
            'episode' => $request->query->get('episode'),
        ]);

        $data = $this->apiClient->getEpisodes($page, $filters);

        return new JsonResponse($data);
    }

    /**
     * Récupère un épisode spécifique par son ID
     * 
     * @param int $id Identifiant unique de l'épisode
     * @return JsonResponse Données complètes de l'épisode
     * @throws ResourceNotFoundException Si l'épisode n'existe pas
     */
    #[Route('/{id}', name: 'show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(int $id): JsonResponse
    {
        $data = $this->apiClient->getEpisode($id);

        return new JsonResponse($data);
    }

    /**
     * Récupère plusieurs épisodes par leurs IDs en une seule requête
     * 
     * Exemple d'utilisation: GET /api/episodes/multiple?ids=1,2,3
     * 
     * @param Request $request Requête HTTP contenant le paramètre 'ids' (séparés par des virgules)
     * @return JsonResponse Tableau contenant les données de tous les épisodes demandés
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
        $data = $this->apiClient->getMultipleEpisodes($ids);

        return new JsonResponse($data);
    }
}
