<?php

namespace App\Exception;

/**
 * Exception levée quand une ressource n'existe pas (erreur 404)
 * 
 * Exemple : GET /api/characters/99999 (n'existe pas)
 * 
 * Pourquoi cette classe est "vide" ?
 * - Son NOM identifie clairement le problème : "Ressource non trouvée"
 * - Permet de différencier une 404 d'autres types d'erreurs
 * - On peut la "catch" spécifiquement dans le code
 * 
 * Sans cette classe, on devrait faire :
 *   if ($exception->getMessage() === "Not found") { ... }
 * 
 * Avec cette classe, on peut faire :
 *   catch (ResourceNotFoundException $e) { ... }
 * 
 */
class ResourceNotFoundException extends \Exception {}
