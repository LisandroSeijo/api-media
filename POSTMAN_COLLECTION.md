# 🧪 Colección Postman - API REST OAuth2.0

## Importar en Postman

Puedes importar este archivo JSON directamente en Postman:

1. Abre Postman
2. Click en "Import"
3. Selecciona este archivo o pega el contenido
4. Los endpoints estarán listos para usar

---

## Collection JSON

```json
{
  "info": {
    "name": "Laravel API REST OAuth2.0",
    "description": "API REST con autenticación OAuth2.0 usando Laravel Passport",
    "schema": "https://schema.getpostman.com/json/collection/v2.1.0/collection.json"
  },
  "auth": {
    "type": "bearer",
    "bearer": [
      {
        "key": "token",
        "value": "{{access_token}}",
        "type": "string"
      }
    ]
  },
  "variable": [
    {
      "key": "base_url",
      "value": "http://localhost:8000/api/v1",
      "type": "string"
    },
    {
      "key": "access_token",
      "value": "",
      "type": "string"
    }
  ],
  "item": [
    {
      "name": "Auth",
      "item": [
        {
          "name": "Register",
          "event": [
            {
              "listen": "test",
              "script": {
                "exec": [
                  "if (pm.response.code === 201) {",
                  "    var jsonData = pm.response.json();",
                  "    pm.collectionVariables.set('access_token', jsonData.data.access_token);",
                  "}"
                ]
              }
            }
          ],
          "request": {
            "method": "POST",
            "header": [
              {
                "key": "Content-Type",
                "value": "application/json"
              },
              {
                "key": "Accept",
                "value": "application/json"
              }
            ],
            "body": {
              "mode": "raw",
              "raw": "{\n  \"name\": \"John Doe\",\n  \"email\": \"john@example.com\",\n  \"password\": \"password123\",\n  \"password_confirmation\": \"password123\"\n}"
            },
            "url": {
              "raw": "{{base_url}}/register",
              "host": ["{{base_url}}"],
              "path": ["register"]
            }
          }
        },
        {
          "name": "Login",
          "event": [
            {
              "listen": "test",
              "script": {
                "exec": [
                  "if (pm.response.code === 200) {",
                  "    var jsonData = pm.response.json();",
                  "    pm.collectionVariables.set('access_token', jsonData.data.access_token);",
                  "}"
                ]
              }
            }
          ],
          "request": {
            "method": "POST",
            "header": [
              {
                "key": "Content-Type",
                "value": "application/json"
              },
              {
                "key": "Accept",
                "value": "application/json"
              }
            ],
            "body": {
              "mode": "raw",
              "raw": "{\n  \"email\": \"john@example.com\",\n  \"password\": \"password123\"\n}"
            },
            "url": {
              "raw": "{{base_url}}/login",
              "host": ["{{base_url}}"],
              "path": ["login"]
            }
          }
        },
        {
          "name": "Get User",
          "request": {
            "auth": {
              "type": "bearer",
              "bearer": [
                {
                  "key": "token",
                  "value": "{{access_token}}",
                  "type": "string"
                }
              ]
            },
            "method": "GET",
            "header": [
              {
                "key": "Accept",
                "value": "application/json"
              }
            ],
            "url": {
              "raw": "{{base_url}}/user",
              "host": ["{{base_url}}"],
              "path": ["user"]
            }
          }
        },
        {
          "name": "Logout",
          "request": {
            "auth": {
              "type": "bearer",
              "bearer": [
                {
                  "key": "token",
                  "value": "{{access_token}}",
                  "type": "string"
                }
              ]
            },
            "method": "POST",
            "header": [
              {
                "key": "Accept",
                "value": "application/json"
              }
            ],
            "url": {
              "raw": "{{base_url}}/logout",
              "host": ["{{base_url}}"],
              "path": ["logout"]
            }
          }
        }
      ]
    },
    {
      "name": "Posts",
      "item": [
        {
          "name": "Get All Posts",
          "request": {
            "auth": {
              "type": "bearer",
              "bearer": [
                {
                  "key": "token",
                  "value": "{{access_token}}",
                  "type": "string"
                }
              ]
            },
            "method": "GET",
            "header": [
              {
                "key": "Accept",
                "value": "application/json"
              }
            ],
            "url": {
              "raw": "{{base_url}}/posts",
              "host": ["{{base_url}}"],
              "path": ["posts"]
            }
          }
        },
        {
          "name": "Get Post by ID",
          "request": {
            "auth": {
              "type": "bearer",
              "bearer": [
                {
                  "key": "token",
                  "value": "{{access_token}}",
                  "type": "string"
                }
              ]
            },
            "method": "GET",
            "header": [
              {
                "key": "Accept",
                "value": "application/json"
              }
            ],
            "url": {
              "raw": "{{base_url}}/posts/1",
              "host": ["{{base_url}}"],
              "path": ["posts", "1"]
            }
          }
        },
        {
          "name": "Create Post",
          "request": {
            "auth": {
              "type": "bearer",
              "bearer": [
                {
                  "key": "token",
                  "value": "{{access_token}}",
                  "type": "string"
                }
              ]
            },
            "method": "POST",
            "header": [
              {
                "key": "Content-Type",
                "value": "application/json"
              },
              {
                "key": "Accept",
                "value": "application/json"
              }
            ],
            "body": {
              "mode": "raw",
              "raw": "{\n  \"title\": \"Mi Nuevo Post\",\n  \"content\": \"Este es el contenido de mi nuevo post\"\n}"
            },
            "url": {
              "raw": "{{base_url}}/posts",
              "host": ["{{base_url}}"],
              "path": ["posts"]
            }
          }
        },
        {
          "name": "Update Post",
          "request": {
            "auth": {
              "type": "bearer",
              "bearer": [
                {
                  "key": "token",
                  "value": "{{access_token}}",
                  "type": "string"
                }
              ]
            },
            "method": "PUT",
            "header": [
              {
                "key": "Content-Type",
                "value": "application/json"
              },
              {
                "key": "Accept",
                "value": "application/json"
              }
            ],
            "body": {
              "mode": "raw",
              "raw": "{\n  \"title\": \"Título Actualizado\",\n  \"content\": \"Contenido actualizado del post\"\n}"
            },
            "url": {
              "raw": "{{base_url}}/posts/1",
              "host": ["{{base_url}}"],
              "path": ["posts", "1"]
            }
          }
        },
        {
          "name": "Delete Post",
          "request": {
            "auth": {
              "type": "bearer",
              "bearer": [
                {
                  "key": "token",
                  "value": "{{access_token}}",
                  "type": "string"
                }
              ]
            },
            "method": "DELETE",
            "header": [
              {
                "key": "Accept",
                "value": "application/json"
              }
            ],
            "url": {
              "raw": "{{base_url}}/posts/1",
              "host": ["{{base_url}}"],
              "path": ["posts", "1"]
            }
          }
        }
      ]
    },
    {
      "name": "Health Check",
      "request": {
        "method": "GET",
        "header": [
          {
            "key": "Accept",
            "value": "application/json"
          }
        ],
        "url": {
          "raw": "{{base_url}}/health",
          "host": ["{{base_url}}"],
          "path": ["health"]
        }
      }
    }
  ]
}
```

## 📝 Instrucciones de Uso

### 1. Configurar Variables

Después de importar, verifica las variables de la colección:
- `base_url`: http://localhost:8000/api/v1
- `access_token`: Se llenará automáticamente al hacer login/register

### 2. Flujo de Trabajo

1. **Health Check**: Verifica que la API esté funcionando
2. **Register**: Registra un nuevo usuario (el token se guarda automáticamente)
3. **Login**: O inicia sesión con un usuario existente
4. **Usar otros endpoints**: El token se incluirá automáticamente

### 3. Scripts de Test

Los endpoints de Register y Login incluyen scripts que automáticamente guardan el token en la variable `access_token`, por lo que no necesitas copiarlo manualmente.

---

## 🎯 Endpoints Rápidos

| Método | Endpoint | Requiere Auth | Descripción |
|--------|----------|---------------|-------------|
| GET | /health | ❌ | Health check |
| POST | /register | ❌ | Registrar usuario |
| POST | /login | ❌ | Iniciar sesión |
| GET | /user | ✅ | Obtener usuario autenticado |
| POST | /logout | ✅ | Cerrar sesión |
| GET | /posts | ✅ | Listar posts |
| GET | /posts/{id} | ✅ | Obtener post específico |
| POST | /posts | ✅ | Crear post |
| PUT | /posts/{id} | ✅ | Actualizar post |
| DELETE | /posts/{id} | ✅ | Eliminar post |
