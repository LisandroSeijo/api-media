<?php

namespace App\Console\Commands;

use Api\Auth\Application\DTOs\RegisterUserDTO;
use Api\Auth\Application\UseCases\CreateAdminUser;
use Illuminate\Console\Command;
use DomainException;
use InvalidArgumentException;

class CreateAdminCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'create:admin 
                            {--name= : The name of the admin user}
                            {--email= : The email of the admin user}
                            {--password= : The password of the admin user}';

    /**
     * The console command description.
     */
    protected $description = 'Create a new admin user';

    /**
     * Execute the console command.
     */
    public function handle(CreateAdminUser $createAdminUser): int
    {
        $this->info('🔐 Creating Admin User');
        $this->newLine();

        // Obtener datos desde opciones o preguntar interactivamente
        $name = $this->option('name') ?: $this->ask('Name');
        $email = $this->option('email') ?: $this->ask('Email');
        $password = $this->option('password') ?: $this->secret('Password (min 6 characters)');

        // Validación básica
        if (empty($name) || empty($email) || empty($password)) {
            $this->error('❌ All fields are required.');
            return self::FAILURE;
        }

        if (strlen($password) < 6) {
            $this->error('❌ Password must be at least 6 characters.');
            return self::FAILURE;
        }

        // Confirmar creación
        $this->newLine();
        $this->table(
            ['Field', 'Value'],
            [
                ['Name', $name],
                ['Email', $email],
                ['Role', 'ADMIN'],
            ]
        );

        if (!$this->confirm('Do you want to create this admin user?', true)) {
            $this->warn('⚠️  Operation cancelled.');
            return self::SUCCESS;
        }

        try {
            // Crear DTO
            $dto = new RegisterUserDTO(
                name: $name,
                email: $email,
                password: $password
            );

            // Ejecutar Use Case
            $user = $createAdminUser->execute($dto);

            $this->newLine();
            $this->info('✅ Admin user created successfully!');
            $this->newLine();
            $this->line("ID: {$user->getId()}");
            $this->line("Name: {$user->getName()}");
            $this->line("Email: {$user->getEmail()->value()}");
            $this->line("Role: {$user->getRole()->value}");
            $this->newLine();

            return self::SUCCESS;

        } catch (DomainException $e) {
            $this->error("❌ {$e->getMessage()}");
            return self::FAILURE;
        } catch (InvalidArgumentException $e) {
            $this->error("❌ Validation error: {$e->getMessage()}");
            return self::FAILURE;
        } catch (\Exception $e) {
            $this->error("❌ An error occurred: {$e->getMessage()}");
            return self::FAILURE;
        }
    }
}
