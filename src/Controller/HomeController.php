<?php

namespace App\Controller;


use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class HomeController
{
    /**
     * Page d'accueil - Documentation de l'API
     */
    #[Route('/', name: 'home', methods: ['GET'])]
    public function index(): JsonResponse
    {
        return new JsonResponse([
            'name' => 'Rick and Morty API Wrapper',
            'version' => '1.0.0',
            'description' => 'A Symfony wrapper for the Rick and Morty API',
            'endpoints' => [
                'characters' => [
                    'list' => 'GET /api/characters',
                    'list_with_pagination' => 'GET /api/characters?page=2',
                    'list_with_filters' => 'GET /api/characters?name=rick&status=alive',
                    'single' => 'GET /api/characters/{id}',
                    'multiple' => 'GET /api/characters/multiple?ids=1,2,3',
                ],
                'locations' => [
                    'list' => 'GET /api/locations',
                    'list_with_pagination' => 'GET /api/locations?page=2',
                    'list_with_filters' => 'GET /api/locations?name=earth&type=planet',
                    'single' => 'GET /api/locations/{id}',
                    'multiple' => 'GET /api/locations/multiple?ids=1,2,3',
                ],
                'episodes' => [
                    'list' => 'GET /api/episodes',
                    'list_with_pagination' => 'GET /api/episodes?page=2',
                    'list_with_filters' => 'GET /api/episodes?name=pilot&episode=S01E01',
                    'single' => 'GET /api/episodes/{id}',
                    'multiple' => 'GET /api/episodes/multiple?ids=1,2,3',
                ],
            ],
            'filters' => [
                'characters' => ['name', 'status', 'species', 'type', 'gender'],
                'locations' => ['name', 'type', 'dimension'],
                'episodes' => ['name', 'episode'],
            ],
            'examples' => [
                'Get all characters' => 'http://localhost:8080/api/characters',
                'Get Rick Sanchez' => 'http://localhost:8080/api/characters/1',
                'Search alive Ricks' => 'http://localhost:8080/api/characters?name=rick&status=alive',
                'Get multiple characters' => 'http://localhost:8080/api/characters/multiple?ids=1,2,3',
                'Get all locations' => 'http://localhost:8080/api/locations',
                'Get all episodes' => 'http://localhost:8080/api/episodes',
            ],
        ]);
    }
}
