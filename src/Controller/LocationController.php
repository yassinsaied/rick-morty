<?php

namespace App\Controller;

use App\Service\RickMortyApiClient;
use App\Exception\ResourceNotFoundException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/locations', name: 'api_locations_')]
class LocationController
{
    public function __construct(
        private readonly RickMortyApiClient $apiClient
    ) {}

    /**
     * Get all locations with pagination and filters
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
     * Get a single location by ID
     */
    #[Route('/{id}', name: 'show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(int $id): JsonResponse
    {
        $data = $this->apiClient->getLocation($id);

        return new JsonResponse($data);
    }

    /**
     * Get multiple locations by IDs
     * Example: /api/locations/multiple?ids=1,2,3
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
