<?php

namespace App\Tests\Unit\Controller;

use App\Controller\UserController;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserControllerTest extends TestCase
{
    private EntityManagerInterface&MockObject $entityManager;
    private UserPasswordHasherInterface&MockObject $passwordHasher;
    private EntityRepository&MockObject $userRepository;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->passwordHasher = $this->createMock(UserPasswordHasherInterface::class);
        $this->userRepository = $this->createMock(EntityRepository::class);

        $this->entityManager->method('getRepository')
            ->willReturn($this->userRepository);
    }

    private function createController(): UserController
    {
        return new UserController(
            $this->entityManager,
            $this->passwordHasher
        );
    }

    private function createMockUser(int $id, string $email, array $roles = ['ROLE_USER']): User
    {
        $user = $this->createMock(User::class);
        $user->method('getId')->willReturn($id);
        $user->method('getEmail')->willReturn($email);
        $user->method('getFirstName')->willReturn('Test');
        $user->method('getLastName')->willReturn('User');
        $user->method('getRoles')->willReturn($roles);
        $user->method('getCreatedAt')->willReturn(new \DateTimeImmutable());
        return $user;
    }

    public function testListReturnsAllUsers(): void
    {
        $users = [
            $this->createMockUser(1, 'user1@test.com'),
            $this->createMockUser(2, 'user2@test.com'),
        ];

        $this->userRepository->method('findAll')->willReturn($users);

        $controller = $this->createController();
        $response = $controller->list();

        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertCount(2, $data);
    }

    public function testShowReturnsUser(): void
    {
        $user = $this->createMockUser(1, 'test@test.com');
        $this->userRepository->method('find')->willReturn($user);

        $controller = $this->createController();
        $response = $controller->show(1);

        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertEquals(1, $data['id']);
        $this->assertEquals('test@test.com', $data['email']);
    }

    public function testShowReturnsNotFound(): void
    {
        $this->userRepository->method('find')->willReturn(null);

        $controller = $this->createController();
        $response = $controller->show(999);

        $this->assertEquals(404, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertEquals('User not found', $data['error']);
    }

    public function testUpdateUser(): void
    {
        $user = $this->createMock(User::class);
        $user->method('getId')->willReturn(1);
        $user->method('getEmail')->willReturn('updated@test.com');
        $user->method('getFirstName')->willReturn('Updated');
        $user->method('getLastName')->willReturn('User');
        $user->method('getRoles')->willReturn(['ROLE_USER']);

        $this->userRepository->method('find')->willReturn($user);
        $this->entityManager->expects($this->once())->method('flush');

        $controller = $this->createController();

        $request = new Request([], [], [], [], [], [], json_encode([
            'firstName' => 'Updated',
            'email' => 'updated@test.com'
        ]));

        $response = $controller->update(1, $request);

        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertEquals('User updated successfully', $data['message']);
    }

    public function testUpdateUserNotFound(): void
    {
        $this->userRepository->method('find')->willReturn(null);

        $controller = $this->createController();
        $request = new Request([], [], [], [], [], [], json_encode([
            'firstName' => 'Test'
        ]));

        $response = $controller->update(999, $request);

        $this->assertEquals(404, $response->getStatusCode());
    }

    public function testUpdateUserPassword(): void
    {
        $user = $this->createMock(User::class);
        $user->method('getId')->willReturn(1);

        $this->userRepository->method('find')->willReturn($user);
        $this->passwordHasher->expects($this->once())
            ->method('hashPassword')
            ->willReturn('new_hashed_password');

        $user->expects($this->once())
            ->method('setPassword')
            ->with('new_hashed_password');

        $controller = $this->createController();

        $request = new Request([], [], [], [], [], [], json_encode([
            'password' => 'new_password123'
        ]));

        $response = $controller->update(1, $request);

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testDeleteUser(): void
    {
        $user = $this->createMockUser(1, 'test@test.com');
        $this->userRepository->method('find')->willReturn($user);

        $this->entityManager->expects($this->once())->method('remove')->with($user);
        $this->entityManager->expects($this->once())->method('flush');

        $controller = $this->createController();
        $response = $controller->delete(1);

        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertEquals('User deleted successfully', $data['message']);
    }

    public function testDeleteUserNotFound(): void
    {
        $this->userRepository->method('find')->willReturn(null);

        $controller = $this->createController();
        $response = $controller->delete(999);

        $this->assertEquals(404, $response->getStatusCode());
    }
}
