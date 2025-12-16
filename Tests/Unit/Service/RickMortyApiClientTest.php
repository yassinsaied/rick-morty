<?php
// ========================================
// tests/Unit/Service/RickMortyApiClientTest.php
// ========================================

namespace App\Tests\Unit\Service;

use App\Service\RickMortyApiClient;
use App\Exception\RickMortyApiException;
use App\Exception\ResourceNotFoundException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

class RickMortyApiClientTest extends TestCase
{
    private function createClient(array $responses): RickMortyApiClient
    {
        $mockResponses = array_map(
            fn($r) => new MockResponse($r['body'], $r['info'] ?? []),
            $responses
        );
        $httpClient = new MockHttpClient($mockResponses);
        return new RickMortyApiClient($httpClient);
    }

    public function testGetCharactersSuccess(): void
    {
        $expectedData = [
            'info' => ['count' => 826, 'pages' => 42],
            'results' => [
                ['id' => 1, 'name' => 'Rick Sanchez', 'status' => 'Alive']
            ]
        ];

        $client = $this->createClient([
            ['body' => json_encode($expectedData), 'info' => ['http_code' => 200]]
        ]);

        $result = $client->getCharacters();

        $this->assertEquals($expectedData, $result);
        $this->assertArrayHasKey('info', $result);
        $this->assertArrayHasKey('results', $result);
    }

    public function testGetCharactersWithPagination(): void
    {
        $expectedData = [
            'info' => ['count' => 826, 'pages' => 42],
            'results' => []
        ];

        $client = $this->createClient([
            ['body' => json_encode($expectedData), 'info' => ['http_code' => 200]]
        ]);

        $result = $client->getCharacters(2);

        $this->assertEquals($expectedData, $result);
    }

    public function testGetCharactersWithFilters(): void
    {
        $expectedData = [
            'info' => ['count' => 29, 'pages' => 2],
            'results' => [
                ['id' => 1, 'name' => 'Rick Sanchez', 'status' => 'Alive']
            ]
        ];

        $client = $this->createClient([
            ['body' => json_encode($expectedData), 'info' => ['http_code' => 200]]
        ]);

        $result = $client->getCharacters(1, ['name' => 'rick', 'status' => 'alive']);

        $this->assertEquals($expectedData, $result);
    }

    public function testGetCharacterSuccess(): void
    {
        $expectedData = [
            'id' => 1,
            'name' => 'Rick Sanchez',
            'status' => 'Alive',
            'species' => 'Human'
        ];

        $client = $this->createClient([
            ['body' => json_encode($expectedData), 'info' => ['http_code' => 200]]
        ]);

        $result = $client->getCharacter(1);

        $this->assertEquals($expectedData, $result);
        $this->assertEquals(1, $result['id']);
    }

    public function testGetCharacterNotFound(): void
    {
        $this->expectException(ResourceNotFoundException::class);

        $client = $this->createClient([
            ['body' => '{"error":"Not found"}', 'info' => ['http_code' => 404]]
        ]);

        $client->getCharacter(99999);
    }

    public function testGetMultipleCharacters(): void
    {
        $expectedData = [
            ['id' => 1, 'name' => 'Rick Sanchez'],
            ['id' => 2, 'name' => 'Morty Smith']
        ];

        $client = $this->createClient([
            ['body' => json_encode($expectedData), 'info' => ['http_code' => 200]]
        ]);

        $result = $client->getMultipleCharacters([1, 2]);

        $this->assertCount(2, $result);
        $this->assertEquals('Rick Sanchez', $result[0]['name']);
    }

    public function testGetLocationsSuccess(): void
    {
        $expectedData = [
            'info' => ['count' => 126, 'pages' => 7],
            'results' => [
                ['id' => 1, 'name' => 'Earth', 'type' => 'Planet']
            ]
        ];

        $client = $this->createClient([
            ['body' => json_encode($expectedData), 'info' => ['http_code' => 200]]
        ]);

        $result = $client->getLocations();

        $this->assertEquals($expectedData, $result);
    }

    public function testGetEpisodesSuccess(): void
    {
        $expectedData = [
            'info' => ['count' => 51, 'pages' => 3],
            'results' => [
                ['id' => 1, 'name' => 'Pilot', 'episode' => 'S01E01']
            ]
        ];

        $client = $this->createClient([
            ['body' => json_encode($expectedData), 'info' => ['http_code' => 200]]
        ]);

        $result = $client->getEpisodes();

        $this->assertEquals($expectedData, $result);
    }

    public function testApiError500(): void
    {
        $this->expectException(RickMortyApiException::class);

        $client = $this->createClient([
            ['body' => '{"error":"Server error"}', 'info' => ['http_code' => 500]]
        ]);

        $client->getCharacters();
    }
}
