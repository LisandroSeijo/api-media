<?php

namespace Api\Auth\Infrastructure\Persistence\Eloquent\Repositories;

use Api\Auth\Domain\Entities\User;
use Api\Auth\Domain\Repositories\UserRepositoryInterface;
use Api\Auth\Domain\ValueObjects\Email;
use Api\Auth\Domain\ValueObjects\Password;
use Api\Auth\Infrastructure\Persistence\Eloquent\Models\UserModel;
use DateTime;

/**
 * Eloquent User Repository
 * 
 * Implementación concreta del repositorio de usuarios usando Eloquent ORM.
 * Mapea entre UserModel (Eloquent) y User (Domain Entity).
 */
class EloquentUserRepository implements UserRepositoryInterface
{
    /**
     * {@inheritDoc}
     */
    public function findByEmail(Email $email): ?User
    {
        $model = UserModel::where('email', $email->value())->first();
        
        return $model ? $this->toDomain($model) : null;
    }

    /**
     * {@inheritDoc}
     */
    public function findById(int $id): ?User
    {
        $model = UserModel::find($id);
        
        return $model ? $this->toDomain($model) : null;
    }

    /**
     * {@inheritDoc}
     */
    public function save(User $user): User
    {
        // Si el usuario tiene ID, es una actualización
        $model = $user->getId()
            ? UserModel::find($user->getId())
            : new UserModel();

        // Si no se encontró el modelo, crear uno nuevo
        if (!$model) {
            $model = new UserModel();
        }

        // Mapear datos de la entidad de dominio al modelo
        $model->name = $user->getName();
        $model->email = $user->getEmail()->value();
        $model->password = $user->getPassword()->hash();
        
        // Guardar en la base de datos
        $model->save();

        // Retornar la entidad de dominio actualizada
        return $this->toDomain($model);
    }

    /**
     * {@inheritDoc}
     */
    public function existsByEmail(Email $email): bool
    {
        return UserModel::where('email', $email->value())->exists();
    }

    /**
     * Mapea un UserModel (Eloquent) a una entidad User (Domain)
     * 
     * @param UserModel $model
     * @return User
     */
    private function toDomain(UserModel $model): User
    {
        return new User(
            id: $model->id,
            name: $model->name,
            email: new Email($model->email),
            password: Password::fromHash($model->password),
            createdAt: $model->created_at ? DateTime::createFromFormat('Y-m-d H:i:s', $model->created_at) : null
        );
    }
}
