```mermaid
sequenceDiagram
    participant Client as HTTP Client
    participant Middleware as EnsureUserIsAdmin
    participant Controller as PostRegisterUserController
    participant Spec as RegisterUserSpecification
    participant DTO as RegisterUserDTO
    participant UseCase as RegisterUser
    participant Repo as UserRepositoryInterface
    participant VOs as Value Objects<br/>(Email, Password)
    participant Entity as User
    participant Role as Role Enum
    participant Exception as DomainException

    %% === AUTENTICACIÓN ===
    Client->>+Middleware: POST /api/v1/register<br/>Authorization: Bearer {token}<br/>{"name": "John", "email": "john@example.com", "password": "secret123"}
    
    Note over Middleware: Verificar autenticación (auth:api)
    Middleware->>Middleware: Obtiene user del token
    
    alt Usuario no autenticado
        Middleware-->>Client: 401 Unauthenticated
    end
    
    Note over Middleware: Verificar rol ADMIN
    Middleware->>Entity: user->isAdmin()
    Entity-->>Middleware: boolean
    
    alt Usuario no es ADMIN
        Middleware-->>Client: 403 Forbidden<br/>{"message": "Admin privileges required"}
    end
    
    Note over Middleware: Usuario es ADMIN, continuar
    Middleware->>+Controller: Request pasa middleware
    
    %% === VALIDACIÓN ===
    Note over Controller: Extraer parámetros del request
    Controller->>Controller: name = "John"<br/>email = "john@example.com"<br/>password = "secret123"
    
    Controller->>+Spec: hasErrors(name, email, password)
    Spec->>Spec: Valida NameSpecification
    Spec->>Spec: Valida EmailSpecification
    Spec->>Spec: Valida RegisterPasswordSpecification
    Spec-->>-Controller: false (sin errores)
    
    alt Hay errores de validación
        Controller->>+Spec: getValidationErrors(name, email, password)
        Spec-->>-Controller: ["email" => "error msg", ...]
        Controller-->>Client: 422 JSON Response<br/>{"success": false, "errors": {...}}
    end
    
    %% === FLUJO PRINCIPAL ===
    Note over Controller: Validación exitosa
    
    Controller->>+DTO: new RegisterUserDTO(name, email, password)
    DTO-->>-Controller: dto
    
    Controller->>+UseCase: execute(dto)
    
    Note over UseCase: Verificar que email no exista
    UseCase->>+VOs: new Email(dto.email)
    VOs->>VOs: Validar formato de email
    VOs-->>-UseCase: emailVO
    
    UseCase->>+Repo: existsByEmail(emailVO)
    Repo->>Repo: SELECT * FROM users<br/>WHERE email = 'john@example.com'
    
    alt Email ya existe
        Repo-->>UseCase: true
        
        Note over UseCase: Email duplicado
        UseCase->>+Exception: throw new DomainException(<br/>"Email ya está registrado")
        Exception-->>-UseCase: exception
        
        UseCase-->>-Controller: DomainException
        Controller-->>-Client: 400 Bad Request<br/>{"success": false,<br/>"message": "Email ya está registrado"}
    
    else Email no existe
        Repo-->>-UseCase: false
        
        Note over UseCase: Email disponible, crear usuario
        
        UseCase->>+VOs: Password::hash(dto.password)
        VOs->>VOs: bcrypt("secret123", rounds: 10)
        VOs-->>-UseCase: passwordVO (hashed)
        
        UseCase->>+Role: Role::USER (default)
        Role-->>-UseCase: roleEnum
        
        Note over UseCase: Crear entidad User
        UseCase->>+Entity: new User(<br/>name: dto.name,<br/>email: emailVO,<br/>password: passwordVO,<br/>role: Role::USER)
        Entity->>Entity: Validar invariantes
        Entity-->>-UseCase: user
        
        Note over UseCase: Persistir usuario
        UseCase->>+Repo: save(user)
        Repo->>Repo: INSERT INTO users<br/>(name, email, password, role)<br/>VALUES (...)
        Repo->>Entity: setId(generatedId)
        Entity-->>Repo: user con ID
        Repo-->>-UseCase: void
        
        UseCase-->>-Controller: user
        
        Note over Controller: Construye respuesta HTTP
        Controller->>Entity: getId(), getName(), etc.
        Entity-->>Controller: datos del usuario
        
        Controller-->>-Client: 201 Created<br/>{"success": true,<br/>"message": "Usuario registrado",<br/>"data": {<br/>  "id": 42,<br/>  "name": "John",<br/>  "email": "john@example.com",<br/>  "role": "user"<br/>}}
    end
```