<?php

namespace App\Controller;

use App\Service\RickMortyApiClient;
use App\Exception\ResourceNotFoundException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/characters', name: 'api_characters_')]
class CharacterController
{
    public function __construct(
        private readonly RickMortyApiClient $apiClient
    ) {}

    /**
     * Get all characters with pagination and filters
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
     * Get a single character by ID
     */
    #[Route('/{id}', name: 'show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(int $id): JsonResponse
    {
        $data = $this->apiClient->getCharacter($id);
        return new JsonResponse($data);
    }

    /**
     * Get multiple characters by IDs
     * Example: /api/characters/multiple?ids=1,2,3
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
