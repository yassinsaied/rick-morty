<?php

namespace App\EventListener;

use App\Exception\ResourceNotFoundException;
use App\Exception\RickMortyApiException;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

/**
 * AVEC PHP 8+ : Utilisation de l'attribut #[AsEventListener]
 * 
 * Avantages :
 * ✅ Plus besoin de configuration dans services.yaml
 * ✅ Code plus moderne et lisible
 * ✅ Tout est dans la même classe
 */
#[AsEventListener(event: 'kernel.exception')]
class ExceptionListener
{
    public function __invoke(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();
        $response = new JsonResponse();

        // Gérer chaque type d'exception
        if ($exception instanceof ResourceNotFoundException) {
            $response->setData([
                'error' => 'Not Found',
                'message' => $exception->getMessage(),
            ]);
            $response->setStatusCode(404);
        } elseif ($exception instanceof RickMortyApiException) {
            $response->setData([
                'error' => 'External API Error',
                'message' => $exception->getMessage(),
            ]);
            $response->setStatusCode(502);
        } elseif ($exception instanceof HttpExceptionInterface) {
            $response->setData([
                'error' => 'HTTP Error',
                'message' => $exception->getMessage(),
            ]);
            $response->setStatusCode($exception->getStatusCode());
        } else {
            $response->setData([
                'error' => 'Internal Server Error',
                'message' => $exception->getMessage(),
            ]);
            $response->setStatusCode(500);
        }

        $event->setResponse($response);
    }
}
