# Softigital Core

**A powerful Laravel package for rapid API development with authentication, code generators, and smart middleware.**

Streamline your Laravel projects with pre-built authentication components, Google OAuth integration, standardized API responses, and intelligent code generators that follow best practices.

[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)
[![Laravel Version](https://img.shields.io/badge/Laravel-11%2B-red.svg)](https://laravel.com)

---

## 📑 Table of Contents

- [Overview](#-overview)
- [Requirements](#-requirements)
- [Installation](#-installation)
- [Quick Start Guide](#-quick-start-guide)
- [Commands Reference](#-commands-reference)
  - [Installation Commands](#installation-commands)
  - [Generator Commands](#generator-commands)
- [Middleware](#-middleware)
- [API Response Utility](#-api-response-utility)
- [Usage Examples](#-usage-examples)
- [Configuration](#-configuration)
- [Testing](#-testing)
- [Security](#-security)
- [Contributing](#-contributing)
- [License](#-license)

---

## 🎯 Overview

Softigital Core is designed to eliminate repetitive boilerplate code and accelerate Laravel API development. Install complete authentication systems with a single command, generate well-structured services and routes instantly, and enjoy auto-configured middleware that just works.

**Key Features:**
- 🔐 Complete authentication system (register, login, profile)
- 🌐 Google OAuth integration with ID token verification
- 📦 Standardized JSON response formatting
- 🛡️ Smart middleware (auto-applied JSON responses, optional auth)
- 🎨 Code generators for routes and services
- 📂 Automatic API route structure (v1 versioning)
- ⚙️ Fully configurable via published config file

---

## 📋 Requirements

- **PHP**: 8.2 or higher
- **Laravel**: 11.0 or higher
- **Laravel Sanctum**: For token-based authentication

---

## 📦 Installation

Install via Composer:

```bash
composer require softigital-dev/core
```

The service provider registers automatically via Laravel's package auto-discovery.

### Optional: Publish Configuration

To customize middleware behavior:

```bash
php artisan vendor:publish --tag=softigital-config
```

This creates `config/softigital-core.php` for middleware configuration.

---

## 🚀 Quick Start Guide

### 1. Install Basic Authentication

Install a complete authentication system with one command:

```bash
php artisan softigital:install auth
```

**What you get:**
- Login endpoint (`POST /api/v1/auth/login`)
- Register endpoint (`POST /api/v1/auth/register`)
- Profile endpoint (`GET /api/v1/auth/me`)
- Complete controllers, services, requests, and routes

### 2. Generate Additional Resources

Create a full CRUD API in seconds:

```bash
# Create complete CRUD structure for a model
php artisan make:crud Post

# Or create individual components:

# Create route file with API resource routes
php artisan make:route posts --controller=PostController --api

# Generate service with repository pattern
php artisan make:service Post --model=Post --repository
```

### 3. Start Using

Your API is ready! The middleware automatically handles JSON responses.

```bash
# Test the registration endpoint
curl -X POST http://localhost/api/v1/auth/register \
  -H "Content-Type: application/json" \
  -d '{"name":"John","email":"john@example.com","password":"secret123"}'
```

---

## 📚 Commands Reference

### Installation Commands

#### `softigital:install {type}`

Install pre-built authentication components.

**Available Types:**

| Type | Description | What Gets Installed |
|------|-------------|---------------------|
| `auth` | Basic authentication | AuthController, AuthService, LoginRequest, RegisterRequest, routes |
| `google-auth` | Google OAuth | GoogleAuthController, GoogleLoginRequest, google config, migration, google/apiclient package |

**Options:**
- `--force` : Overwrite existing files without prompting
- `--skip-migration` : Skip migration publishing and execution (google-auth only)
- `--skip-composer` : Skip composer package installation

**Examples:**

```bash
# Install basic authentication
php artisan softigital:install auth

# Install Google OAuth with force overwrite
php artisan softigital:install google-auth --force

# Install without running migrations
php artisan softigital:install google-auth --skip-migration

# Display help
php artisan softigital:install --help
```

**After installing Google auth, configure `.env`:**
```env
GOOGLE_CLIENT_ID=your-client-id-here
GOOGLE_CLIENT_SECRET=your-client-secret-here
GOOGLE_REDIRECT_URI=your-redirect-uri-here
```

---

### Generator Commands

#### `make:route {name}`

Create a new route file in `routes/v1/` with automatic registration.

**Options:**
- `--controller=Name` : Specify controller and import it
- `--resource` : Generate full resourceful routes (index, create, store, show, edit, update, destroy)
- `--api` : Generate API resource routes (excludes create, edit)

**Examples:**

```bash
# Basic route file
php artisan make:route posts

# With controller
php artisan make:route posts --controller=PostController

# API resource routes (recommended for APIs)
php artisan make:route posts --controller=PostController --api

# Full resource routes
php artisan make:route products --controller=ProductController --resource
```

**Generated file (`routes/v1/posts.php`):**
```php
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PostController;

Route::apiResource('posts', PostController::class);
```

The route file is automatically registered in `routes/v1/api.php`.

---

#### `make:service {name}`

Generate service classes in `app/Services/` with subdirectory support.

**Options:**
- `--model=Name` : Inject model dependency
- `--repository` : Generate CRUD repository pattern methods

**Subdirectory Support:**
Use `/`, `\`, or `.` notation for nested directories:

```bash
# All these work the same:
php artisan make:service Auth/Login
php artisan make:service Auth\Login
php artisan make:service Auth.Login
```

**Examples:**

```bash
# Basic service
php artisan make:service Post

# Service with model
php artisan make:service Post --model=Post

# Full repository pattern with CRUD
php artisan make:service Post --model=Post --repository

# Organized in subdirectories
php artisan make:service Auth/Login --model=User --repository
php artisan make:service Blog/Post --repository
php artisan make:service Payment/Stripe
```

**Generated structure:**
```
app/Services/
├── PostService.php
├── Auth/
│   ├── LoginService.php
│   └── RegisterService.php
└── Blog/
    └── PostService.php
```

---

#### `make:crud {name}`

Generate a complete CRUD structure for a model with all necessary files in one command.

**What Gets Generated:**
- ✅ Model in `app/Models/`
- ✅ Migration in `database/migrations/`
- ✅ Service in `app/Services/`
- ✅ Controller in `app/Http/Controllers/`
- ✅ Store Request (POST) in `app/Http/Requests/`
- ✅ Update Request (PATCH) in `app/Http/Requests/`
- ✅ Resource in `app/Http/Resources/`
- ✅ Route file in `routes/v1/`

**Options:**
- `--api-prefix=v1` : API version prefix for routes (default: v1)
- `--force` : Overwrite existing files without prompting

**Examples:**

```bash
# Generate CRUD for Post model
php artisan make:crud Post

# Generate with different API version
php artisan make:crud Product --api-prefix=v2

# Overwrite existing files
php artisan make:crud BlogPost --force
```

**Generated Files for `php artisan make:crud Post`:**
```
app/
├── Models/Post.php (empty - define your fields)
├── Services/PostService.php (full CRUD methods)
├── Http/
│   ├── Controllers/PostController.php (complete REST API)
│   ├── Requests/
│   │   ├── StorePostRequest.php (empty - add validation rules)
│   │   └── UpdatePostRequest.php (empty - add validation rules)
│   └── Resources/PostResource.php (empty - define response shape)
database/migrations/2025_xx_xx_xxxxxx_create_posts_table.php (empty)
routes/v1/posts.php (RESTful routes with auth:sanctum)
```

**Generated Controller Methods:**
- `index()` - GET /posts (list with pagination)
- `show($id)` - GET /posts/{id} (single resource)
- `store(StorePostRequest)` - POST /posts (create)
- `update(UpdatePostRequest, $id)` - PATCH /posts/{id} (update)
- `destroy($id)` - DELETE /posts/{id} (delete)

**Generated Routes:**
```php
Route::middleware('auth:sanctum')->group(function () {
    Route::get('posts', [PostController::class, 'index']);
    Route::get('posts/{id}', [PostController::class, 'show']);
    Route::post('posts', [PostController::class, 'store']);
    Route::patch('posts/{id}', [PostController::class, 'update']);
    Route::delete('posts/{id}', [PostController::class, 'destroy']);
});
```

**Next Steps After Generation:**
1. Define model properties and relationships in `app/Models/Post.php`
2. Add table columns in the migration file and run `php artisan migrate`
3. Define validation rules in `StorePostRequest.php` and `UpdatePostRequest.php`
4. Customize the resource fields in `PostResource.php`
5. Register the route file in `routes/api.php`:
   ```php
   require __DIR__.'/v1/posts.php';
   ```

**Controller Features:**
- Uses service layer for clean separation of concerns
- Returns proper HTTP status codes (200, 201, 404, 500)
- Automatic pagination with metadata for index endpoint
- Uses Resource for consistent response formatting
- Integrates with ApiResponse utility for standardized responses

**Generated service with `--repository` (`app/Services/PostService.php`):**
```php
<?php

namespace App\Services;

use App\Models\Post;

class PostService
{
    public function getAll()
    {
        return Post::all();
    }

    public function findById($id)
    {
        return Post::findOrFail($id);
    }

    public function create(array $data)
    {
        return Post::create($data);
    }

    public function update($id, array $data)
    {
        $post = $this->findById($id);
        $post->update($data);
        return $post;
    }

    public function delete($id)
    {
        $post = $this->findById($id);
        return $post->delete();
    }
}
```

---

## 🛡️ Middleware

The package includes two smart middleware that enhance your API automatically.

### Force JSON Response

Ensures all API requests return JSON (no HTML error pages).

**Auto-Applied:** Automatically applies to all `api/*` routes.

**Configuration (`config/softigital-core.php`):**
```php
'force_json' => [
    'enabled' => true,      // Enable/disable middleware
    'auto_apply' => true,   // Auto-apply to 'api' middleware group
],
```

**What it does:**
- Sets `Accept: application/json` header for all API routes
- Prevents HTML error pages in your API
- Ensures consistent JSON responses for validation errors and exceptions

**Disable globally:**
```php
// config/softigital-core.php
'force_json' => ['enabled' => false],
```

---

### Optional Sanctum Authentication

Allows routes to work for both authenticated and guest users.

**Alias:** `auth.optional`

**Configuration:**
```php
'optional_auth' => [
    'enabled' => true,
],
```

**Usage Example:**
```php
Route::get('/posts', [PostController::class, 'index'])
    ->middleware('auth.optional');
```

**In your controller:**
```php
public function index(Request $request)
{
    $user = $request->user(); // null if guest, User if authenticated
    
    if ($user) {
        return ApiResponse::success('Your posts', $user->posts);
    }
    
    return ApiResponse::success('Public posts', Post::where('public', true)->get());
}
```

**Use Cases:**
- Public feeds with personalized content for logged-in users
- Like/favorite features that work without login
- Content that varies based on authentication status

---

## 📦 API Response Utility

The package includes `ApiResponse` utility for standardized JSON responses.

### Available Methods

```php
use App\Utils\ApiResponse;

// Success (200)
ApiResponse::success('Operation successful', ['key' => 'value']);

// Created (201)
ApiResponse::created('Resource created', $resource);

// Bad Request (400)
ApiResponse::badRequest('Invalid input', ['field' => 'error message']);

// Not Found (404)
ApiResponse::notFound('Resource not found');

// Forbidden (403)
ApiResponse::forbidden('Access denied');

// Validation Error (422)
ApiResponse::validationError('Validation failed', $validator->errors());

// Server Error (500)
ApiResponse::error('Something went wrong');
```

### Response Format

All responses follow this structure:

```json
{
  "status": 200,
  "message": "Operation successful",
  "meta": null,
  "data": {
    "key": "value"
  }
}
```

### Usage in Controllers

```php
class PostController extends Controller
{
    public function __construct(private PostService $postService) {}

    public function index()
    {
        $posts = $this->postService->getAll();
        return ApiResponse::success('Posts retrieved', $posts);
    }

    public function store(StorePostRequest $request)
    {
        $post = $this->postService->create($request->validated());
        return ApiResponse::created('Post created successfully', $post);
    }

    public function show($id)
    {
        $post = $this->postService->findById($id);
        
        if (!$post) {
            return ApiResponse::notFound('Post not found');
        }
        
        return ApiResponse::success('Post retrieved', $post);
    }
}
```

---

## 💻 Usage Examples

### Authentication Flow

#### Register a New User

**Request:**
```bash
POST /api/v1/auth/register
Content-Type: application/json

{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "securepass123"
}
```

**Response:**
```json
{
  "status": 201,
  "message": "User registered successfully",
  "meta": null,
  "data": {
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com"
    },
    "token": "1|abc123xyz..."
  }
}
```

---

#### Login

**Request:**
```bash
POST /api/v1/auth/login
Content-Type: application/json

{
  "email": "john@example.com",
  "password": "securepass123"
}
```

**Response:**
```json
{
  "status": 200,
  "message": "Login successful",
  "meta": null,
  "data": {
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com"
    },
    "token": "2|def456uvw..."
  }
}
```

---

#### Get Authenticated User Profile

**Request:**
```bash
GET /api/v1/auth/me
Authorization: Bearer 2|def456uvw...
```

**Response:**
```json
{
  "status": 200,
  "message": "User retrieved successfully",
  "meta": null,
  "data": {
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com"
    }
  }
}
```

---

### Google OAuth Flow

**Request:**
```bash
POST /api/v1/auth/google
Content-Type: application/json

{
  "id_token": "google-id-token-from-frontend"
}
```

**Response:**
```json
{
  "status": 200,
  "message": "User authenticated successfully",
  "meta": null,
  "data": {
    "token": "3|ghi789rst...",
    "user": {
      "id": 2,
      "name": "Jane Smith",
      "email": "jane@gmail.com",
      "google_id": "1234567890"
    },
    "first_time": true
  }
}
```

---

### Complete CRUD Example

Building a full REST API for posts:

```bash
# 1. Create route file with API resource routes
php artisan make:route posts --controller=PostController --api

# 2. Generate service with repository pattern
php artisan make:service Post --model=Post --repository

# 3. Create controller
php artisan make:controller PostController --api

# 4. Create form requests
php artisan make:request StorePostRequest
php artisan make:request UpdatePostRequest
```

**Wire up the controller:**

```php
// app/Http/Controllers/PostController.php
namespace App\Http\Controllers;

use App\Services\PostService;
use App\Http\Requests\StorePostRequest;
use App\Http\Requests\UpdatePostRequest;
use App\Utils\ApiResponse;

class PostController extends Controller
{
    public function __construct(private PostService $postService) {}

    public function index()
    {
        return ApiResponse::success(
            'Posts retrieved', 
            $this->postService->getAll()
        );
    }

    public function store(StorePostRequest $request)
    {
        $post = $this->postService->create($request->validated());
        return ApiResponse::created('Post created', $post);
    }

    public function show($id)
    {
        $post = $this->postService->findById($id);
        return ApiResponse::success('Post retrieved', $post);
    }

    public function update(UpdatePostRequest $request, $id)
    {
        $post = $this->postService->update($id, $request->validated());
        return ApiResponse::success('Post updated', $post);
    }

    public function destroy($id)
    {
        $this->postService->delete($id);
        return ApiResponse::success('Post deleted');
    }
}
```

**Your routes are ready:**
- `GET /api/v1/posts` - List all posts
- `POST /api/v1/posts` - Create post
- `GET /api/v1/posts/{id}` - Show post
- `PUT /api/v1/posts/{id}` - Update post
- `DELETE /api/v1/posts/{id}` - Delete post

---

## ⚙️ Configuration

### Route Structure

The package automatically creates a versioned API structure:

**Generated Structure:**
```
routes/
└── v1/
    ├── api.php         # Main route file
    ├── auth.php        # Auth routes (if installed)
    └── google.php      # Google OAuth routes (if installed)
```

### Bootstrap Configuration

Your `bootstrap/app.php` is automatically updated:

```php
return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/health',
        then: function () {
            Route::prefix('api/v1')
                ->middleware('api')
                ->group(base_path('routes/v1/api.php'));
        }
    )
    // ... rest of configuration
```

### Middleware Configuration

Publish and edit `config/softigital-core.php`:

```php
return [
    'force_json' => [
        'enabled' => true,
        'auto_apply' => true,  // Auto-apply to 'api' middleware group
    ],

    'optional_auth' => [
        'enabled' => true,
    ],
];
```

### Environment Variables (Google OAuth)

Add to `.env` after installing Google authentication:

```env
GOOGLE_CLIENT_ID=your-google-client-id
GOOGLE_CLIENT_SECRET=your-google-client-secret
GOOGLE_REDIRECT_URI=http://localhost:8000/auth/google/callback
```

---

## 🧪 Testing

### Example Test Cases

```php
namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthenticationTest extends TestCase
{
    public function test_user_can_register(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => ['user', 'token'],
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
        ]);
    }

    public function test_user_can_login(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('password123'),
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['data' => ['token', 'user']]);
    }

    public function test_authenticated_user_can_get_profile(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withToken($token)
            ->getJson('/api/v1/auth/me');

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'user' => [
                        'email' => $user->email,
                    ],
                ],
            ]);
    }
}
```

---

## 🔒 Security

### Token Management

- Uses Laravel Sanctum for secure token-based authentication
- Tokens stored in `personal_access_tokens` table
- Each login creates a new token
- Revoke tokens by deleting from database or calling `$token->delete()`

### Password Security

- Passwords hashed with bcrypt
- Minimum 8 characters enforced
- Never logs or displays passwords

### Google OAuth Security

- Google ID tokens verified server-side
- Uses official Google API Client library
- `google_id` stored for account linking
- Handles edge cases (existing email conflicts)

**Best Practices:**
```php
// Always verify Google tokens server-side
$client = new Google_Client(['client_id' => config('google.client_id')]);
$payload = $client->verifyIdToken($idToken);
```

---

## 🤝 Contributing

Contributions are welcome! Please follow these steps:

1. Fork the repository
2. Create a feature branch: `git checkout -b feature/amazing-feature`
3. Commit changes: `git commit -m 'Add amazing feature'`
4. Push to branch: `git push origin feature/amazing-feature`
5. Open a Pull Request

### Development Setup

```bash
git clone https://github.com/softigital-dev/core.git
cd core
composer install
```

---

## 📄 License

This package is open-sourced software licensed under the [MIT license](LICENSE).

---

## 🆘 Support

- **GitHub Issues**: [https://github.com/softigital-dev/core/issues](https://github.com/softigital-dev/core/issues)
- **Author**: Youssef Ehab - youssefehab.ofice@gmail.com

---

## 🎯 Roadmap

- [ ] Email verification flow
- [ ] Password reset functionality
- [ ] Two-factor authentication (2FA)
- [ ] Additional OAuth providers (Facebook, GitHub)
- [ ] Role-based access control (RBAC)
- [ ] API rate limiting utilities

---

<p align="center">Made with ❤️ by Youssef Ehab</p>
