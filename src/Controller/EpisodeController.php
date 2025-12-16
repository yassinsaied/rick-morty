<?php

namespace App\Controller;

use App\Service\RickMortyApiClient;
use App\Exception\ResourceNotFoundException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/episodes', name: 'api_episodes_')]
class EpisodeController
{
    public function __construct(
        private readonly RickMortyApiClient $apiClient
    ) {}

    /**
     * Get all episodes with pagination and filters
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
     * Get a single episode by ID
     */
    #[Route('/{id}', name: 'show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(int $id): JsonResponse
    {
        $data = $this->apiClient->getEpisode($id);

        return new JsonResponse($data);
    }

    /**
     * Get multiple episodes by IDs
     * Example: /api/episodes/multiple?ids=1,2,3
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
