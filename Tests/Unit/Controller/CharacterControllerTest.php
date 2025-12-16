<?php

// ========================================
// tests/Unit/Controller/CharacterControllerTest.php
// ========================================

namespace App\Tests\Unit\Controller;

use App\Controller\CharacterController;
use App\Service\RickMortyApiClient;
use App\Exception\ResourceNotFoundException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

class CharacterControllerTest extends TestCase
{
    private function createController(array $mockData): CharacterController
    {
        $mockClient = $this->createMock(RickMortyApiClient::class);
        $mockClient->method('getCharacters')->willReturn($mockData);
        $mockClient->method('getCharacter')->willReturn($mockData);
        $mockClient->method('getMultipleCharacters')->willReturn($mockData);

        return new CharacterController($mockClient);
    }

    public function testListReturnsCharacters(): void
    {
        $mockData = [
            'info' => ['count' => 826, 'pages' => 42],
            'results' => [
                ['id' => 1, 'name' => 'Rick Sanchez']
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

    public function testListWithPagination(): void
    {
        $mockData = [
            'info' => ['count' => 826, 'pages' => 42],
            'results' => []
        ];

        $controller = $this->createController($mockData);
        $request = new Request(['page' => '2']);

        $response = $controller->list($request);

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testListWithFilters(): void
    {
        $mockData = [
            'info' => ['count' => 29, 'pages' => 2],
            'results' => [
                ['id' => 1, 'name' => 'Rick Sanchez', 'status' => 'Alive']
            ]
        ];

        $controller = $this->createController($mockData);
        $request = new Request(['name' => 'rick', 'status' => 'alive']);

        $response = $controller->list($request);

        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('results', $data);
    }

    public function testShowReturnsCharacter(): void
    {
        $mockData = [
            'id' => 1,
            'name' => 'Rick Sanchez',
            'status' => 'Alive'
        ];

        $controller = $this->createController($mockData);

        $response = $controller->show(1);

        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertEquals(1, $data['id']);
        $this->assertEquals('Rick Sanchez', $data['name']);
    }

    public function testMultipleReturnsCharacters(): void
    {
        $mockData = [
            ['id' => 1, 'name' => 'Rick Sanchez'],
            ['id' => 2, 'name' => 'Morty Smith']
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
        $controller = new CharacterController($mockClient);
        $request = new Request();

        $controller->multiple($request);
    }
}
