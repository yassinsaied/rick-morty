<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Contrôleur pour l'authentification et l'inscription des utilisateurs
 * 
 * Gère l'enregistrement de nouveaux utilisateurs.
 * L'endpoint de login est géré automatiquement par LexikJWTAuthenticationBundle.
 */
#[Route('/api/auth', name: 'api_auth_')]
class AuthController
{
    /**
     * @param EntityManagerInterface $entityManager Gestionnaire d'entités Doctrine
     * @param UserPasswordHasherInterface $passwordHasher Service de hachage des mots de passe
     * @param ValidatorInterface $validator Service de validation Symfony
     */
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly ValidatorInterface $validator
    ) {}

    /**
     * Enregistre un nouvel utilisateur dans le système
     * 
     * Champs requis dans le corps de la requête JSON:
     * - email: adresse email unique de l'utilisateur
     * - password: mot de passe (sera hashé avant stockage)
     * - firstName: prénom
     * - lastName: nom de famille
     * - roles (optionnel): tableau des rôles (défaut: ['ROLE_USER'])
     * 
     * @param Request $request Requête HTTP contenant les données d'inscription en JSON
     * @return JsonResponse Confirmation d'inscription avec les données de l'utilisateur créé
     */
    #[Route('/register', name: 'register', methods: ['POST'])]
    public function register(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // Validation
        if (!isset($data['email'], $data['password'], $data['firstName'], $data['lastName'])) {
            return new JsonResponse([
                'error' => 'Missing required fields',
                'required' => ['email', 'password', 'firstName', 'lastName']
            ], 400);
        }

        // Check if user already exists
        $existingUser = $this->entityManager->getRepository(User::class)
            ->findOneBy(['email' => $data['email']]);

        if ($existingUser) {
            return new JsonResponse([
                'error' => 'User already exists'
            ], 409);
        }

        // Create new user
        $user = new User();
        $user->setEmail($data['email']);
        $user->setFirstName($data['firstName']);
        $user->setLastName($data['lastName']);

        // Hash password
        $hashedPassword = $this->passwordHasher->hashPassword($user, $data['password']);
        $user->setPassword($hashedPassword);

        // Set roles (default: ROLE_USER)
        $roles = $data['roles'] ?? ['ROLE_USER'];
        $user->setRoles($roles);

        // Validate
        $errors = $this->validator->validate($user);
        if (count($errors) > 0) {
            return new JsonResponse([
                'error' => 'Validation failed',
                'details' => (string) $errors
            ], 400);
        }

        // Save
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return new JsonResponse([
            'message' => 'User created successfully',
            'user' => [
                'id' => $user->getId(),
                'email' => $user->getEmail(),
                'firstName' => $user->getFirstName(),
                'lastName' => $user->getLastName(),
                'roles' => $user->getRoles()
            ]
        ], 201);
    }

    /**
     * Endpoint de connexion (géré automatiquement par LexikJWTAuthenticationBundle)
     * 
     * POST /api/auth/login avec le corps JSON:
     * {
     *   "username": "email@example.com",
     *   "password": "votre_mot_de_passe"
     * }
     * 
     * Retourne un token JWT en cas de succès:
     * {
     *   "token": "eyJ0eXAiOiJKV1QiLCJhbGc..."
     * }
     */
}
