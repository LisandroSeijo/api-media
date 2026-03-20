<?php

declare(strict_types=1);

namespace Tests\Unit\Auth\UseCases;

use Api\Auth\Application\DTOs\RegisterUserDTO;
use Api\Auth\Application\UseCases\RegisterUser;
use Api\Auth\Domain\Entities\User;
use Api\Auth\Domain\Repositories\UserRepositoryInterface;
use Api\Auth\Domain\ValueObjects\Email;
use Api\Auth\Domain\ValueObjects\Password;
use Api\Auth\Domain\ValueObjects\Role;
use DomainException;
use Tests\TestCase;
use Mockery;

class RegisterUserUseCaseTest extends TestCase
{
    private UserRepositoryInterface $userRepository;
    private RegisterUser $registerUser;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->userRepository = Mockery::mock(UserRepositoryInterface::class);
        $this->registerUser = new RegisterUser($this->userRepository);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_successfully_registers_new_user(): void
    {
        $dto = new RegisterUserDTO(
            name: 'John Doe',
            email: 'john@example.com',
            password: 'password123'
        );

        $this->userRepository
            ->shouldReceive('existsByEmail')
            ->once()
            ->with(Mockery::on(fn($email) => $email instanceof Email && $email->value() === 'john@example.com'))
            ->andReturn(false);

        $this->userRepository
            ->shouldReceive('save')
            ->once()
            ->with(Mockery::on(function ($user) {
                return $user instanceof User
                    && $user->getName() === 'John Doe'
                    && $user->getEmail()->value() === 'john@example.com'
                    && $user->getRole() === Role::USER;
            }))
            ->andReturn(new User(
                id: 1,
                name: 'John Doe',
                email: new Email('john@example.com'),
                password: Password::fromPlain('password123'),
                role: Role::USER
            ));

        $result = $this->registerUser->execute($dto);

        $this->assertInstanceOf(User::class, $result);
        $this->assertEquals(1, $result->getId());
        $this->assertEquals('John Doe', $result->getName());
        $this->assertEquals('john@example.com', $result->getEmail()->value());
        $this->assertEquals(Role::USER, $result->getRole());
    }

    public function test_throws_exception_when_email_already_exists(): void
    {
        $dto = new RegisterUserDTO(
            name: 'John Doe',
            email: 'existing@example.com',
            password: 'password123'
        );

        $this->userRepository
            ->shouldReceive('existsByEmail')
            ->once()
            ->with(Mockery::on(fn($email) => $email instanceof Email && $email->value() === 'existing@example.com'))
            ->andReturn(true);

        $this->userRepository
            ->shouldNotReceive('save');

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Email already registered');

        $this->registerUser->execute($dto);
    }

    public function test_assigns_default_user_role(): void
    {
        $dto = new RegisterUserDTO(
            name: 'John Doe',
            email: 'john@example.com',
            password: 'password123'
        );

        $this->userRepository
            ->shouldReceive('existsByEmail')
            ->once()
            ->andReturn(false);

        $this->userRepository
            ->shouldReceive('save')
            ->once()
            ->with(Mockery::on(function ($user) {
                return $user->getRole() === Role::USER;
            }))
            ->andReturn(new User(
                id: 1,
                name: 'John Doe',
                email: new Email('john@example.com'),
                password: Password::fromPlain('password123'),
                role: Role::USER
            ));

        $result = $this->registerUser->execute($dto);

        $this->assertTrue($result->isUser());
        $this->assertFalse($result->isAdmin());
    }
}
