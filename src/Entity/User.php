<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Entité représentant un utilisateur du système
 * 
 * Cette classe implémente les interfaces Symfony pour l'authentification JWT.
 * Elle stocke les informations d'identité et les rôles de chaque utilisateur.
 */
#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`users`')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    /** Identifiant unique auto-généré */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /** Adresse email unique servant d'identifiant de connexion */
    #[ORM\Column(length: 180, unique: true)]
    private ?string $email = null;

    /** Rôles attribués à l'utilisateur (ROLE_USER, ROLE_ADMIN, etc.) */
    #[ORM\Column]
    private array $roles = [];

    /** Mot de passe hashé (jamais stocké en clair) */
    #[ORM\Column]
    private ?string $password = null;

    /** Prénom de l'utilisateur */
    #[ORM\Column(length: 100)]
    private ?string $firstName = null;

    /** Nom de famille de l'utilisateur */
    #[ORM\Column(length: 100)]
    private ?string $lastName = null;

    /** Date et heure de création du compte */
    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    /**
     * Initialise la date de création lors de l'instanciation
     */
    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    /**
     * Récupère l'identifiant unique de l'utilisateur
     * 
     * @return int|null ID de l'utilisateur ou null si pas encore persisté
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Récupère l'adresse email de l'utilisateur
     * 
     * @return string|null Email ou null
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * Définit l'adresse email de l'utilisateur
     * 
     * @param string $email Nouvelle adresse email
     * @return static Instance courante pour chaînage
     */
    public function setEmail(string $email): static
    {
        $this->email = $email;
        return $this;
    }

    /**
     * Retourne l'identifiant utilisé pour l'authentification (email)
     * 
     * Méthode requise par l'interface UserInterface de Symfony
     * 
     * @return string Email de l'utilisateur
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * Récupère tous les rôles de l'utilisateur
     * 
     * Garantit que ROLE_USER est toujours présent et que les rôles sont uniques
     * 
     * @return array<string> Liste des rôles
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // Garantit que chaque utilisateur a au moins ROLE_USER
        $roles[] = 'ROLE_USER';
        return array_unique($roles);
    }

    /**
     * Définit les rôles de l'utilisateur
     * 
     * @param array<string> $roles Nouveaux rôles à attribuer
     * @return static Instance courante pour chaînage
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;
        return $this;
    }

    /**
     * Récupère le mot de passe hashé
     * 
     * @return string Mot de passe hashé
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * Définit le mot de passe hashé
     * 
     * Attention: ce mot de passe doit être hashé AVANT d'être passé à cette méthode
     * 
     * @param string $password Mot de passe hashé
     * @return static Instance courante pour chaînage
     */
    public function setPassword(string $password): static
    {
        $this->password = $password;
        return $this;
    }

    /**
     * Efface les données sensibles temporaires
     * 
     * Méthode requise par l'interface UserInterface.
     * Utilisée pour effacer le mot de passe en clair si stocké temporairement.
     */
    public function eraseCredentials(): void
    {
        // Aucune donnée sensible temporaire à effacer
    }

    /**
     * Récupère le prénom de l'utilisateur
     * 
     * @return string|null Prénom ou null
     */
    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    /**
     * Définit le prénom de l'utilisateur
     * 
     * @param string $firstName Nouveau prénom
     * @return static Instance courante pour chaînage
     */
    public function setFirstName(string $firstName): static
    {
        $this->firstName = $firstName;
        return $this;
    }

    /**
     * Récupère le nom de famille de l'utilisateur
     * 
     * @return string|null Nom de famille ou null
     */
    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    /**
     * Définit le nom de famille de l'utilisateur
     * 
     * @param string $lastName Nouveau nom de famille
     * @return static Instance courante pour chaînage
     */
    public function setLastName(string $lastName): static
    {
        $this->lastName = $lastName;
        return $this;
    }

    /**
     * Récupère la date de création du compte
     * 
     * @return \DateTimeImmutable|null Date de création ou null
     */
    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * Définit la date de création du compte
     * 
     * @param \DateTimeImmutable $createdAt Nouvelle date de création
     * @return static Instance courante pour chaînage
     */
    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }
}
