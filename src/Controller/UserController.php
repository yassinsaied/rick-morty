<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Contrôleur pour la gestion des utilisateurs (Administration)
 * 
 * Tous les endpoints de ce contrôleur nécessitent le rôle ROLE_ADMIN.
 * Permet de lister, afficher, modifier et supprimer des utilisateurs.
 */
#[Route('/api/users', name: 'api_users_')]
#[IsGranted('ROLE_ADMIN')]
class UserController
{
    /**
     * @param EntityManagerInterface $entityManager Gestionnaire d'entités Doctrine
     * @param UserPasswordHasherInterface $passwordHasher Service de hachage des mots de passe
     */
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserPasswordHasherInterface $passwordHasher
    ) {}

    /**
     * Liste tous les utilisateurs du système (Admin uniquement)
     * 
     * @return JsonResponse Tableau contenant tous les utilisateurs avec leurs informations
     */
    #[Route('', name: 'list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $users = $this->entityManager->getRepository(User::class)->findAll();

        $data = array_map(fn(User $user) => [
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'firstName' => $user->getFirstName(),
            'lastName' => $user->getLastName(),
            'roles' => $user->getRoles(),
            'createdAt' => $user->getCreatedAt()->format('Y-m-d H:i:s')
        ], $users);

        return new JsonResponse($data);
    }

    /**
     * Récupère un utilisateur spécifique par son ID (Admin uniquement)
     * 
     * @param int $id Identifiant unique de l'utilisateur
     * @return JsonResponse Données complètes de l'utilisateur ou erreur 404
     */
    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $user = $this->entityManager->getRepository(User::class)->find($id);

        if (!$user) {
            return new JsonResponse(['error' => 'User not found'], 404);
        }

        return new JsonResponse([
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'firstName' => $user->getFirstName(),
            'lastName' => $user->getLastName(),
            'roles' => $user->getRoles(),
            'createdAt' => $user->getCreatedAt()->format('Y-m-d H:i:s')
        ]);
    }

    /**
     * Met à jour un utilisateur existant (Admin uniquement)
     * 
     * Champs modifiables dans le corps de la requête JSON:
     * - email: nouvelle adresse email
     * - firstName: nouveau prénom
     * - lastName: nouveau nom de famille
     * - roles: nouveaux rôles
     * - password: nouveau mot de passe (sera hashé)
     * 
     * @param int $id Identifiant de l'utilisateur à modifier
     * @param Request $request Requête HTTP contenant les données à modifier en JSON
     * @return JsonResponse Confirmation de modification avec les nouvelles données
     */
    #[Route('/{id}', name: 'update', methods: ['PUT', 'PATCH'])]
    public function update(int $id, Request $request): JsonResponse
    {
        $user = $this->entityManager->getRepository(User::class)->find($id);

        if (!$user) {
            return new JsonResponse(['error' => 'User not found'], 404);
        }

        $data = json_decode($request->getContent(), true);

        if (isset($data['email'])) {
            $user->setEmail($data['email']);
        }
        if (isset($data['firstName'])) {
            $user->setFirstName($data['firstName']);
        }
        if (isset($data['lastName'])) {
            $user->setLastName($data['lastName']);
        }
        if (isset($data['roles'])) {
            $user->setRoles($data['roles']);
        }
        if (isset($data['password'])) {
            $hashedPassword = $this->passwordHasher->hashPassword($user, $data['password']);
            $user->setPassword($hashedPassword);
        }

        $this->entityManager->flush();

        return new JsonResponse([
            'message' => 'User updated successfully',
            'user' => [
                'id' => $user->getId(),
                'email' => $user->getEmail(),
                'firstName' => $user->getFirstName(),
                'lastName' => $user->getLastName(),
                'roles' => $user->getRoles()
            ]
        ]);
    }

    /**
     * Supprime un utilisateur du système (Admin uniquement)
     * 
     * @param int $id Identifiant de l'utilisateur à supprimer
     * @return JsonResponse Confirmation de suppression ou erreur 404
     */
    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $user = $this->entityManager->getRepository(User::class)->find($id);

        if (!$user) {
            return new JsonResponse(['error' => 'User not found'], 404);
        }

        $this->entityManager->remove($user);
        $this->entityManager->flush();

        return new JsonResponse([
            'message' => 'User deleted successfully'
        ]);
    }
}
