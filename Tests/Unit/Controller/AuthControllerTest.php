<?php

namespace App\Tests\Unit\Controller;

use App\Controller\AuthController;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use PHPUnit\Framework\MockObject\MockObject;

class AuthControllerTest extends TestCase
{
    private EntityManagerInterface&MockObject $entityManager;
    private UserPasswordHasherInterface&MockObject $passwordHasher;
    private ValidatorInterface&MockObject $validator;
    private EntityRepository&MockObject $userRepository;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->passwordHasher = $this->createMock(UserPasswordHasherInterface::class);
        $this->validator = $this->createMock(ValidatorInterface::class);
        $this->userRepository = $this->createMock(EntityRepository::class);

        $this->entityManager->method('getRepository')
            ->willReturn($this->userRepository);
    }

    private function createController(): AuthController
    {
        return new AuthController(
            $this->entityManager,
            $this->passwordHasher,
            $this->validator
        );
    }

    public function testRegisterSuccess(): void
    {
        // Mock: Pas d'utilisateur existant
        $this->userRepository->method('findOneBy')
            ->willReturn(null);

        // Mock: Password hasher
        $this->passwordHasher->method('hashPassword')
            ->willReturn('hashed_password');

        // Mock: Validation réussie
        $this->validator->method('validate')
            ->willReturn(new ConstraintViolationList());

        // Mock: Entity manager
        $this->entityManager->expects($this->once())
            ->method('persist');
        $this->entityManager->expects($this->once())
            ->method('flush');

        $controller = $this->createController();

        $request = new Request([], [], [], [], [], [], json_encode([
            'email' => 'test@example.com',
            'password' => 'password123',
            'firstName' => 'Test',
            'lastName' => 'User'
        ]));

        $response = $controller->register($request);

        $this->assertEquals(201, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertEquals('User created successfully', $data['message']);
        $this->assertArrayHasKey('user', $data);
    }

    public function testRegisterMissingFields(): void
    {
        $controller = $this->createController();

        $request = new Request([], [], [], [], [], [], json_encode([
            'email' => 'test@example.com'
            // Missing password, firstName, lastName
        ]));

        $response = $controller->register($request);

        $this->assertEquals(400, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertEquals('Missing required fields', $data['error']);
    }

    public function testRegisterUserAlreadyExists(): void
    {
        // Mock: Utilisateur existant
        $existingUser = $this->createMock(User::class);
        $this->userRepository->method('findOneBy')
            ->willReturn($existingUser);

        $controller = $this->createController();

        $request = new Request([], [], [], [], [], [], json_encode([
            'email' => 'existing@example.com',
            'password' => 'password123',
            'firstName' => 'Test',
            'lastName' => 'User'
        ]));

        $response = $controller->register($request);

        $this->assertEquals(409, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertEquals('User already exists', $data['error']);
    }

    public function testRegisterWithDefaultRoles(): void
    {
        $this->userRepository->method('findOneBy')->willReturn(null);
        $this->passwordHasher->method('hashPassword')->willReturn('hashed');
        $this->validator->method('validate')->willReturn(new ConstraintViolationList());

        $controller = $this->createController();

        $request = new Request([], [], [], [], [], [], json_encode([
            'email' => 'test@example.com',
            'password' => 'password123',
            'firstName' => 'Test',
            'lastName' => 'User'
        ]));

        $response = $controller->register($request);

        $this->assertEquals(201, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertContains('ROLE_USER', $data['user']['roles']);
    }

    public function testRegisterWithCustomRoles(): void
    {
        $this->userRepository->method('findOneBy')->willReturn(null);
        $this->passwordHasher->method('hashPassword')->willReturn('hashed');
        $this->validator->method('validate')->willReturn(new ConstraintViolationList());

        $controller = $this->createController();

        $request = new Request([], [], [], [], [], [], json_encode([
            'email' => 'admin@example.com',
            'password' => 'password123',
            'firstName' => 'Admin',
            'lastName' => 'User',
            'roles' => ['ROLE_ADMIN']
        ]));

        $response = $controller->register($request);

        $this->assertEquals(201, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertContains('ROLE_ADMIN', $data['user']['roles']);
    }
}
