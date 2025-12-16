<?php

// ========================================
// tests/Unit/Controller/LocationControllerTest.php
// ========================================

namespace App\Tests\Unit\Controller;

use App\Controller\LocationController;
use App\Service\RickMortyApiClient;
use App\Exception\ResourceNotFoundException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

class LocationControllerTest extends TestCase
{
    private function createController(array $mockData): LocationController
    {
        $mockClient = $this->createMock(RickMortyApiClient::class);
        $mockClient->method('getLocations')->willReturn($mockData);
        $mockClient->method('getLocation')->willReturn($mockData);
        $mockClient->method('getMultipleLocations')->willReturn($mockData);

        return new LocationController($mockClient);
    }

    public function testListReturnsLocations(): void
    {
        $mockData = [
            'info' => ['count' => 126, 'pages' => 7],
            'results' => [
                ['id' => 1, 'name' => 'Earth', 'type' => 'Planet']
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
            'info' => ['count' => 10, 'pages' => 1],
            'results' => [
                ['id' => 1, 'name' => 'Earth', 'type' => 'Planet']
            ]
        ];

        $controller = $this->createController($mockData);
        $request = new Request(['name' => 'earth', 'type' => 'planet']);

        $response = $controller->list($request);

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testShowReturnsLocation(): void
    {
        $mockData = [
            'id' => 1,
            'name' => 'Earth',
            'type' => 'Planet',
            'dimension' => 'Dimension C-137'
        ];

        $controller = $this->createController($mockData);

        $response = $controller->show(1);

        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertEquals(1, $data['id']);
        $this->assertEquals('Earth', $data['name']);
    }

    public function testMultipleReturnsLocations(): void
    {
        $mockData = [
            ['id' => 1, 'name' => 'Earth'],
            ['id' => 2, 'name' => 'Citadel of Ricks']
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
        $controller = new LocationController($mockClient);
        $request = new Request();

        $controller->multiple($request);
    }
}
