<?php

namespace App\EventListener;

use App\Exception\ResourceNotFoundException;
use App\Exception\RickMortyApiException;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

/**
 * Gestionnaire global des exceptions de l'API
 * 
 * Ce listener intercepte toutes les exceptions levées dans l'application
 * et les convertit en réponses JSON standardisées avec les codes HTTP appropriés.
 * 
 * Il permet de centraliser la gestion des erreurs et d'assurer une cohérence
 * dans le format des réponses d'erreur retournées aux clients de l'API.
 */
#[AsEventListener(event: 'kernel.exception')]
class ExceptionListener
{
    /**
     * Traite l'exception et génère une réponse JSON appropriée
     * 
     * @param ExceptionEvent $event L'événement contenant l'exception levée
     */
    public function __invoke(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        // Déterminer le code de statut et le type d'erreur selon l'exception
        [$statusCode, $errorType] = $this->getStatusCodeAndType($exception);

        // Construire la réponse JSON
        $response = new JsonResponse([
            'error' => $errorType,
            'message' => $exception->getMessage(),
        ], $statusCode);

        // Remplacer la réponse par défaut par notre réponse JSON
        $event->setResponse($response);
    }

    /**
     * Détermine le code HTTP et le type d'erreur en fonction de l'exception
     * 
     * @param \Throwable $exception L'exception à analyser
     * @return array{int, string} Un tableau contenant [code HTTP, type d'erreur]
     */
    private function getStatusCodeAndType(\Throwable $exception): array
    {
        return match (true) {
            // Ressource non trouvée (ex: personnage, épisode, location)
            $exception instanceof ResourceNotFoundException => [
                Response::HTTP_NOT_FOUND,
                'Not Found'
            ],

            // Erreur de communication avec l'API externe Rick & Morty
            $exception instanceof RickMortyApiException => [
                Response::HTTP_BAD_GATEWAY,
                'External API Error'
            ],

            // Exception HTTP Symfony (400, 403, 405, etc.)
            $exception instanceof HttpExceptionInterface => [
                $exception->getStatusCode(),
                'HTTP Error'
            ],

            // Toutes les autres exceptions non gérées
            default => [
                Response::HTTP_INTERNAL_SERVER_ERROR,
                'Internal Server Error'
            ]
        };
    }
}
