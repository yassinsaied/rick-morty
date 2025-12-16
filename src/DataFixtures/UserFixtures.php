<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher
    ) {}

    public function load(ObjectManager $manager): void
    {
        // Admin user
        $admin = new User();
        $admin->setEmail('admin@rickmorty.com');
        $admin->setFirstName('Admin');
        $admin->setLastName('User');
        $admin->setRoles(['ROLE_ADMIN', 'ROLE_USER']);
        $admin->setPassword($this->passwordHasher->hashPassword($admin, 'admin123'));
        $manager->persist($admin);

        // Regular users
        $users = [
            ['Rick', 'Sanchez', 'rick@rickmorty.com', 'rick123'],
            ['Morty', 'Smith', 'morty@rickmorty.com', 'morty123'],
            ['Summer', 'Smith', 'summer@rickmorty.com', 'summer123'],
            ['Beth', 'Smith', 'beth@rickmorty.com', 'beth123'],
            ['Jerry', 'Smith', 'jerry@rickmorty.com', 'jerry123'],
        ];

        foreach ($users as [$firstName, $lastName, $email, $password]) {
            $user = new User();
            $user->setEmail($email);
            $user->setFirstName($firstName);
            $user->setLastName($lastName);
            $user->setRoles(['ROLE_USER']);
            $user->setPassword($this->passwordHasher->hashPassword($user, $password));
            $manager->persist($user);
        }

        $manager->flush();
    }
}
