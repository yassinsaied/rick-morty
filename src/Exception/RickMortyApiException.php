<?php

namespace App\Exception;

/**
 * Exception levée quand il y a un problème avec l'API Rick and Morty
 * (erreur réseau, timeout, erreur 500, etc.)
 * 
 * Pourquoi cette classe est "vide" ?
 * - Elle hérite de toutes les fonctionnalités de \Exception
 * - Son NOM suffit à identifier le TYPE d'erreur
 * - Cela permet de faire : catch (RickMortyApiException $e)
 * - Plus facile à gérer que de vérifier des codes d'erreur
 */
class RickMortyApiException extends \Exception
{
    // On pourrait ajouter des propriétés personnalisées si nécessaire :

    // private ?int $apiStatusCode = null;

    // public function __construct(
    //     string $message,
    //     int $code = 0,
    //     ?\Throwable $previous = null,
    //     ?int $apiStatusCode = null
    // ) {
    //     parent::__construct($message, $code, $previous);
    //     $this->apiStatusCode = $apiStatusCode;
    // }

    // public function getApiStatusCode(): ?int
    // {
    //     return $this->apiStatusCode;
    // }
}
