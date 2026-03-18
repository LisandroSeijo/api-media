# 📚 Doctrine ORM - Guía de Uso

## ¿Qué es Doctrine ORM?

Doctrine es un ORM (Object-Relational Mapper) avanzado para PHP que proporciona persistencia transparente para objetos PHP. Es una alternativa más robusta a Eloquent para proyectos que requieren mayor flexibilidad y potencia.

## 🔧 Configuración

El archivo de configuración está en `config/doctrine.php`. Principales opciones:

### Metadata Drivers

Doctrine soporta varios tipos de metadata:

- **Attributes/Annotations**: Usa atributos PHP en las clases
- **XML**: Archivos XML de configuración
- **YAML**: Archivos YAML de configuración

## 📁 Estructura de Entidades

Crea tus entidades en `app/Entities/`:

```php
<?php

namespace App\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'users')]
class User
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\Column(type: 'string', length: 255)]
    private string $name;

    #[ORM\Column(type: 'string', length: 255, unique: true)]
    private string $email;

    #[ORM\Column(type: 'string')]
    private string $password;

    #[ORM\Column(type: 'datetime')]
    private \DateTime $createdAt;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    // Getters y Setters
    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;
        return $this;
    }

    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }
}
```

## 🗄️ Uso del Entity Manager

### En Controladores

```php
<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Entities\User;
use Doctrine\ORM\EntityManagerInterface;
use Illuminate\Http\Request;

class UserController extends Controller
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function index()
    {
        $users = $this->entityManager
            ->getRepository(User::class)
            ->findAll();

        return response()->json([
            'success' => true,
            'data' => $users
        ]);
    }

    public function store(Request $request)
    {
        $user = new User();
        $user->setName($request->name);
        $user->setEmail($request->email);
        $user->setPassword(bcrypt($request->password));

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return response()->json([
            'success' => true,
            'message' => 'User created successfully',
            'data' => $user
        ], 201);
    }

    public function show($id)
    {
        $user = $this->entityManager
            ->getRepository(User::class)
            ->find($id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $user
        ]);
    }

    public function update(Request $request, $id)
    {
        $user = $this->entityManager
            ->getRepository(User::class)
            ->find($id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }

        $user->setName($request->name ?? $user->getName());
        $user->setEmail($request->email ?? $user->getEmail());

        $this->entityManager->flush();

        return response()->json([
            'success' => true,
            'message' => 'User updated successfully',
            'data' => $user
        ]);
    }

    public function destroy($id)
    {
        $user = $this->entityManager
            ->getRepository(User::class)
            ->find($id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }

        $this->entityManager->remove($user);
        $this->entityManager->flush();

        return response()->json([
            'success' => true,
            'message' => 'User deleted successfully'
        ]);
    }
}
```

## 🔍 Consultas Personalizadas

### Usando QueryBuilder

```php
$queryBuilder = $this->entityManager->createQueryBuilder();

$users = $queryBuilder
    ->select('u')
    ->from(User::class, 'u')
    ->where('u.email LIKE :email')
    ->setParameter('email', '%@example.com')
    ->orderBy('u.createdAt', 'DESC')
    ->setMaxResults(10)
    ->getQuery()
    ->getResult();
```

### Usando DQL (Doctrine Query Language)

```php
$dql = "SELECT u FROM App\Entities\User u WHERE u.email = :email";
$query = $this->entityManager->createQuery($dql);
$query->setParameter('email', 'user@example.com');
$user = $query->getSingleResult();
```

### Repositorios Personalizados

Crea `app/Repositories/UserRepository.php`:

```php
<?php

namespace App\Repositories;

use App\Entities\User;
use Doctrine\ORM\EntityRepository;

class UserRepository extends EntityRepository
{
    public function findByEmail(string $email): ?User
    {
        return $this->findOneBy(['email' => $email]);
    }

    public function findActiveUsers(): array
    {
        return $this->createQueryBuilder('u')
            ->where('u.active = :active')
            ->setParameter('active', true)
            ->orderBy('u.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
```

Configurar en la entidad:

```php
#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: 'users')]
class User
{
    // ...
}
```

## 📝 Comandos de Doctrine

### Migraciones

```bash
# Generar migración desde cambios en entidades
docker-compose exec app php artisan doctrine:migrations:diff

# Ver estado de migraciones
docker-compose exec app php artisan doctrine:migrations:status

# Ejecutar migraciones pendientes
docker-compose exec app php artisan doctrine:migrations:migrate

# Rollback última migración
docker-compose exec app php artisan doctrine:migrations:rollback

# Ver SQL que se ejecutará
docker-compose exec app php artisan doctrine:migrations:migrate --dry-run
```

### Schema

```bash
# Validar el schema
docker-compose exec app php artisan doctrine:schema:validate

# Crear schema desde cero (¡PELIGROSO!)
docker-compose exec app php artisan doctrine:schema:create

# Actualizar schema (¡PELIGROSO!)
docker-compose exec app php artisan doctrine:schema:update --force

# Ver SQL de actualización
docker-compose exec app php artisan doctrine:schema:update --dump-sql
```

### Caché

```bash
# Limpiar caché de metadata
docker-compose exec app php artisan doctrine:clear:metadata:cache

# Limpiar caché de queries
docker-compose exec app php artisan doctrine:clear:query:cache

# Limpiar caché de resultados
docker-compose exec app php artisan doctrine:clear:result:cache
```

### Información

```bash
# Ver todas las entidades mapeadas
docker-compose exec app php artisan doctrine:mapping:info

# Describir una entidad
docker-compose exec app php artisan doctrine:mapping:describe "App\Entities\User"

# Ver todos los comandos de Doctrine
docker-compose exec app php artisan list doctrine
```

## 🔗 Relaciones entre Entidades

### One-to-Many

```php
// Post.php
#[ORM\Entity]
class Post
{
    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'posts')]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id')]
    private User $author;
}

// User.php
#[ORM\Entity]
class User
{
    #[ORM\OneToMany(targetEntity: Post::class, mappedBy: 'author')]
    private Collection $posts;

    public function __construct()
    {
        $this->posts = new ArrayCollection();
    }
}
```

### Many-to-Many

```php
// User.php
#[ORM\ManyToMany(targetEntity: Role::class)]
#[ORM\JoinTable(name: 'user_roles')]
private Collection $roles;
```

## ⚠️ Mejores Prácticas

1. **Siempre usar EntityManager para persistir cambios**
   ```php
   $this->entityManager->persist($entity);
   $this->entityManager->flush();
   ```

2. **Usar transacciones para operaciones múltiples**
   ```php
   $this->entityManager->transactional(function($em) {
       // Operaciones
   });
   ```

3. **Limpiar el EntityManager en procesos largos**
   ```php
   $this->entityManager->clear(); // Libera memoria
   ```

4. **Usar lazy loading con cuidado**
   - Puede causar el problema N+1
   - Usar fetch joins cuando sea necesario

5. **Validar el schema regularmente**
   ```bash
   php artisan doctrine:schema:validate
   ```

## 🆚 Doctrine vs Eloquent

| Característica | Doctrine | Eloquent |
|----------------|----------|----------|
| Complejidad | Mayor | Menor |
| Flexibilidad | Alta | Media |
| Performance | Optimizable | Buena |
| Curva de aprendizaje | Empinada | Suave |
| Unit of Work | Sí | No |
| Identidad de objetos | Sí | No |
| DQL | Sí | No |

## 📚 Recursos Adicionales

- [Documentación Oficial de Doctrine](https://www.doctrine-project.org/projects/doctrine-orm/en/latest/)
- [Laravel Doctrine Documentation](https://www.laraveldoctrine.org/)
- [Doctrine Best Practices](https://www.doctrine-project.org/projects/doctrine-orm/en/latest/reference/best-practices.html)

---

**Desarrollado con ❤️ usando Laravel 12 y Doctrine ORM**
