<?php

// ========================================
// tests/Unit/Controller/EpisodeControllerTest.php
// ========================================

namespace App\Tests\Unit\Controller;

use App\Controller\EpisodeController;
use App\Service\RickMortyApiClient;
use App\Exception\ResourceNotFoundException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

class EpisodeControllerTest extends TestCase
{
    private function createController(array $mockData): EpisodeController
    {
        $mockClient = $this->createMock(RickMortyApiClient::class);
        $mockClient->method('getEpisodes')->willReturn($mockData);
        $mockClient->method('getEpisode')->willReturn($mockData);
        $mockClient->method('getMultipleEpisodes')->willReturn($mockData);

        return new EpisodeController($mockClient);
    }

    public function testListReturnsEpisodes(): void
    {
        $mockData = [
            'info' => ['count' => 51, 'pages' => 3],
            'results' => [
                ['id' => 1, 'name' => 'Pilot', 'episode' => 'S01E01']
            ]
        ];

        $controller = $this->createController($mockData);
        $request = new Request();

        $response = $controller->list($request);

        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('info', $data);
        $this->assertArrayHasKey('results', $data);
    }

    public function testListWithFilters(): void
    {
        $mockData = [
            'info' => ['count' => 1, 'pages' => 1],
            'results' => [
                ['id' => 1, 'name' => 'Pilot', 'episode' => 'S01E01']
            ]
        ];

        $controller = $this->createController($mockData);
        $request = new Request(['name' => 'pilot', 'episode' => 'S01E01']);

        $response = $controller->list($request);

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testShowReturnsEpisode(): void
    {
        $mockData = [
            'id' => 1,
            'name' => 'Pilot',
            'air_date' => 'December 2, 2013',
            'episode' => 'S01E01'
        ];

        $controller = $this->createController($mockData);

        $response = $controller->show(1);

        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertEquals(1, $data['id']);
        $this->assertEquals('Pilot', $data['name']);
    }

    public function testMultipleReturnsEpisodes(): void
    {
        $mockData = [
            ['id' => 1, 'name' => 'Pilot', 'episode' => 'S01E01'],
            ['id' => 2, 'name' => 'Lawnmower Dog', 'episode' => 'S01E02']
        ];

        $controller = $this->createController($mockData);
        $request = new Request(['ids' => '1,2']);

        $response = $controller->multiple($request);

        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertCount(2, $data);
    }

    public function testMultipleThrowsExceptionWithoutIds(): void
    {
        $this->expectException(ResourceNotFoundException::class);

        $mockClient = $this->createMock(RickMortyApiClient::class);
        $controller = new EpisodeController($mockClient);
        $request = new Request();

        $controller->multiple($request);
    }
}
