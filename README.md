# Rick & Morty Symfony JWT API

Une API REST construite avec Symfony pour accéder aux données de l'univers Rick & Morty avec authentification JWT.

## Description

Ce projet expose les données de l'API Rick & Morty (personnages, localités, épisodes) via un proxy API Symfony sécurisé. Inclut un système complet de gestion des utilisateurs avec authentification par JWT et contrôle d'accès basé sur les rôles.

## Technologies utilisées

- PHP 8.1+
- Symfony 6.4
- MYSQL
- Doctrine ORM 3.5
- Lexik JWT Authentication Bundle
- Docker & Docker Compose
- PHPUnit 11.5

## Installation

### Prérequis

- Docker et Docker Compose
- Git

### Étapes

1. Cloner le projet

```bash
git clone <repository>
cd rick-morty-test
```

2. Configurer les variables d'environnement

Le fichier `.env` est déjà inclus dans le projet pour faciliter les tests. En production, créez votre propre fichier `.env.local`.

3. Générer les clés JWT

```bash
docker-compose exec php php bin/console lexik:jwt:generate-keypair
```

Cette commande génère les clés privée et publique dans le dossier `config/jwt/`.

4. Démarrer les conteneurs

```bash
docker-compose up -d
```

5. Installer les dépendances

```bash
docker-compose exec php composer install
```

6. Créer la base de données

```bash
docker-compose exec php php bin/console doctrine:database:create
docker-compose exec php php bin/console doctrine:migrations:migrate
docker-compose exec php php bin/console doctrine:fixtures:load
```

## Démarrage

Les conteneurs se lancent avec :

```bash
docker-compose up -d
```

L'API est accessible sur : http://localhost:8080

Pour arrêter :

```bash
docker-compose down
```

## Tests

Lancer tous les tests :

```bash
php bin/phpunit
```

Lancer un fichier de test spécifique :

```bash
php bin/phpunit tests/Unit/Controller/UserControllerTest.php
```

Lancer un test particulier :

```bash
php bin/phpunit tests/Unit/Controller/UserControllerTest.php --filter testListReturnsAllUsers
```

## Utilisateurs par défaut

```
Email: admin@rickmorty.com
Mot de passe: admin123
Rôle: ROLE_ADMIN
```

## Endpoints principaux

### Authentification

- POST /api/auth/register - Créer un compte
- POST /api/auth/login - Se connecter (obtenir JWT)

### Utilisateurs (Admin only)

- GET /api/users - Lister tous les utilisateurs
- GET /api/users/{id} - Récupérer un utilisateur
- PATCH /api/users/{id} - Modifier un utilisateur
- DELETE /api/users/{id} - Supprimer un utilisateur

### Personnages (JWT required)

- GET /api/characters - Lister avec pagination
- GET /api/characters/{id} - Récupérer un personnage
- GET /api/characters/multiple?ids=1,2,3 - Plusieurs personnages

### Localités & Épisodes

Endpoints similaires aux personnages

## Postman Collection

Pour tester facilement tous les endpoints, importez le fichier `RickMorty_collection.json` situé à la racine du projet dans Postman.

Ce fichier contient :

- Tous les endpoints de l'API organisés par catégories
- Variables d'environnement pré-configurées (base_url, tokens, etc.)
- Scripts de test automatiques pour capturer les tokens JWT après chaque login
- Exemples de requêtes avec les bons paramètres

Après l'import, configurez la variable `base_url` selon votre environnement et utilisez l'endpoint "Login (Admin)" pour récupérer un token et commencer à tester.

## GitHub Actions

Ce projet utilise GitHub Actions pour automatiser les tests et le contrôle de qualité du code. Le workflow de CI/CD est défini dans le fichier `.github/workflows/tests.yml`.

### Workflows disponibles

**Tests automatiques** : À chaque push ou pull request, les workflows suivants s'exécutent automatiquement :

- Lancement de la suite de tests PHPUnit
- Analyse statique du code avec PHPStan
- Vérification du style de code

Cela garantit que le code respecte les standards de qualité avant chaque fusion.
