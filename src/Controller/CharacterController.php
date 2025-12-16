<?php

namespace App\Controller;

use App\Service\RickMortyApiClient;
use App\Exception\ResourceNotFoundException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Contrôleur pour la gestion des personnages de Rick and Morty
 * 
 * Expose les endpoints pour récupérer les personnages depuis l'API externe.
 * Tous les endpoints nécessitent une authentification JWT valide.
 */
#[Route('/api/characters', name: 'api_characters_')]
class CharacterController
{
    /**
     * @param RickMortyApiClient $apiClient Client pour interagir avec l'API Rick and Morty
     */
    public function __construct(
        private readonly RickMortyApiClient $apiClient
    ) {}

    /**
     * Liste tous les personnages avec pagination et filtres optionnels
     * 
     * Paramètres de requête supportés:
     * - page: numéro de la page (défaut: 1)
     * - name: filtrer par nom
     * - status: filtrer par statut (alive, dead, unknown)
     * - species: filtrer par espèce
     * - type: filtrer par type
     * - gender: filtrer par genre (female, male, genderless, unknown)
     * 
     * @param Request $request Requête HTTP contenant les paramètres de pagination et filtres
     * @return JsonResponse Liste des personnages avec informations de pagination
     */
    #[Route('', name: 'list', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        $page = (int) $request->query->get('page', 1);

        // Extract filters from query parameters
        $filters = array_filter([
            'name' => $request->query->get('name'),
            'status' => $request->query->get('status'),
            'species' => $request->query->get('species'),
            'type' => $request->query->get('type'),
            'gender' => $request->query->get('gender'),
        ]);

        $data = $this->apiClient->getCharacters($page, $filters);

        return new JsonResponse($data);
    }

    /**
     * Récupère un personnage spécifique par son ID
     * 
     * @param int $id Identifiant unique du personnage
     * @return JsonResponse Données complètes du personnage
     * @throws ResourceNotFoundException Si le personnage n'existe pas
     */
    #[Route('/{id}', name: 'show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(int $id): JsonResponse
    {
        $data = $this->apiClient->getCharacter($id);
        return new JsonResponse($data);
    }

    /**
     * Récupère plusieurs personnages par leurs IDs en une seule requête
     * 
     * Exemple d'utilisation: GET /api/characters/multiple?ids=1,2,3
     * 
     * @param Request $request Requête HTTP contenant le paramètre 'ids' (séparés par des virgules)
     * @return JsonResponse Tableau contenant les données de tous les personnages demandés
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
        $data = $this->apiClient->getMultipleCharacters($ids);

        return new JsonResponse($data);
    }
}
