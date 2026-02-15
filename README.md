# Softigital Core

A Laravel package that provides essential authentication components and utilities for rapid API development. Streamline your Laravel projects with pre-built authentication, Google OAuth, and standardized API responses.

[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)
[![Laravel Version](https://img.shields.io/badge/Laravel-11%2B-red.svg)](https://laravel.com)

---

## 🚀 Features

- **🔐 Authentication System**: Complete authentication with login, register, and user profile endpoints
- **🌐 Google OAuth**: Ready-to-use Google authentication integration
- **📦 API Response Utilities**: Standardized JSON response formatting
- **🛡️ Smart Middleware**: Auto-applied JSON responses & optional authentication
- **⚙️ Configurable**: Publish and customize middleware behavior
- **⚡ Quick Installation**: One command to scaffold complete features
- **🎯 Laravel 11+ Ready**: Optimized for modern Laravel applications
- **🔄 Route Management**: Automatic v1 API route structure setup

---

## 📋 Requirements

- PHP 8.2 or higher
- Laravel 11.0 or higher
- Laravel Sanctum (for token-based authentication)

---

## 📦 Installation

Install the package via Composer:

```bash
composer require softigital-dev/core
```

The service provider will be automatically registered via Laravel's package auto-discovery.

### Publish Configuration (Optional)

Publish the configuration file to customize middleware behavior:

```bash
php artisan vendor:publish --tag=softigital-config
```

This creates `config/softigital-core.php` where you can enable/disable middleware.

---

## 🛡️ Middleware

The package includes two powerful middleware that enhance your API:

### 1. Force JSON Response (Auto-Applied)

Automatically forces all API requests to accept JSON responses. This prevents HTML error pages and ensures consistent API behavior.

**Configuration** (`config/softigital-core.php`):
```php
'force_json' => [
    'enabled' => true,      // Enable/disable the middleware
    'auto_apply' => true,   // Automatically apply to 'api' middleware group
],
```

**What it does:**
- Sets `Accept: application/json` header for all routes starting with `api/`
- Ensures Laravel returns JSON for validation errors and exceptions
- Prevents accidental HTML responses in your API

**Disable it:**
```php
// config/softigital-core.php
'force_json' => [
    'enabled' => false,
],
```

### 2. Optional Sanctum Authentication

Provides optional authentication for routes that work with or without a user.

**Configuration** (`config/softigital-core.php`):
```php
'optional_auth' => [
    'enabled' => true,
],
```

**Usage in routes:**
```php
// Route works for both authenticated and guest users
Route::get('/posts', [PostController::class, 'index'])
    ->middleware('auth.optional');

// In your controller
public function index(Request $request)
{
    $user = $request->user(); // null if not authenticated
    
    if ($user) {
        // Return personalized content
    } else {
        // Return public content
    }
}
```

**Use cases:**
- Public endpoints with personalized content for logged-in users
- Like/favorite features that work for guests but save for authenticated users
- Content that's different based on authentication status

---

## 🎯 Quick Start

### Display Help

```bash
php artisan softigital:install --help
```

### Install Basic Authentication

Install the complete authentication system with login, register, and user profile endpoints:

```bash
php artisan softigital:install auth
```

**What gets installed:**
- `app/Utils/ApiResponse.php` - Standardized API response utility
- `app/Http/Controllers/Auth/AuthController.php` - Authentication controller
- `app/Http/Requests/LoginRequest.php` - Login validation
- `app/Http/Requests/RegisterRequest.php` - Registration validation
- `app/Services/AuthService.php` - Authentication business logic
- `routes/v1/auth.php` - Authentication routes

**Available endpoints:**
- `POST /api/v1/auth/register` - User registration
- `POST /api/v1/auth/login` - User login
- `GET /api/v1/auth/me` - Get authenticated user (requires Sanctum token)

### Install Google OAuth Authentication

Install Google authentication with ID token verification:

```bash
php artisan softigital:install google-auth
```

**What gets installed:**
- All shared utilities (if not already present)
- `app/Http/Controllers/Auth/GoogleAuthController.php` - Google auth controller
- `app/Http/Requests/GoogleLoginRequest.php` - Google login validation
- `config/google.php` - Google OAuth configuration
- `routes/v1/google.php` - Google authentication routes
- Migration for `google_id` column in users table
- `google/apiclient` composer package (with confirmation)

**Available endpoints:**
- `POST /api/v1/auth/google` - Authenticate with Google ID token

**After installation**, add to your `.env`:
```env
GOOGLE_CLIENT_ID=your-client-id
GOOGLE_CLIENT_SECRET=your-client-secret
GOOGLE_REDIRECT_URI=your-redirect-uri
```

---

## 🎨 Command Options

### Available Options

| Option | Description |
|--------|-------------|
| `--force` | Overwrite existing files without prompting |
| `--skip-migration` | Skip publishing and running migrations (google-auth only) |
| `--skip-composer` | Skip automatic composer package installation |
| `--help`, `-h` | Display detailed help information |

### Examples

```bash
# Install with force overwrite
php artisan softigital:install auth --force

# Install Google auth without running migrations
php artisan softigital:install google-auth --skip-migration

# Install without composer package prompt
php artisan softigital:install google-auth --skip-composer

# Combine multiple options
php artisan softigital:install google-auth --force --skip-migration
```

---

## 📂 What Gets Configured

### Route Structure

The package automatically sets up a versioned API route structure:

**Before:**
```
routes/
  └── api.php  (or may not exist)
```

**After:**
```
routes/
  └── v1/
      ├── api.php
      ├── auth.php  (if auth installed)
      └── google.php  (if google-auth installed)
```

### Bootstrap Configuration

Your `bootstrap/app.php` is automatically updated to use the v1 route structure:

```php
return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/health',
        then: function () {
            Route::prefix('api/v1')
                ->middleware('api')
                ->group(base_path('routes/v1/api.php'));
        }
    )
```

### API Routes

Routes are automatically included in `routes/v1/api.php`:

```php
<?php

require __DIR__.'/auth.php';  // Added by auth installation
require __DIR__.'/google.php';  // Added by google-auth installation
```

---

## 🔧 Usage Examples

### Authentication Flow

#### 1. Register a New User

```http
POST /api/v1/auth/register
Content-Type: application/json

{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "securepassword123"
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
    "token": "1|abc123..."
  }
}
```

#### 2. Login

```http
POST /api/v1/auth/login
Content-Type: application/json

{
  "email": "john@example.com",
  "password": "securepassword123"
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
    "token": "2|xyz789..."
  }
}
```

#### 3. Get Authenticated User

```http
GET /api/v1/auth/me
Authorization: Bearer 2|xyz789...
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

### Google OAuth Flow

#### Authenticate with Google

```http
POST /api/v1/auth/google
Content-Type: application/json

{
  "id_token": "google-id-token-from-client"
}
```

**Response:**
```json
{
  "status": 200,
  "message": "User authenticated successfully",
  "meta": null,
  "data": {
    "token": "3|token123...",
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

### Using ApiResponse Utility

The package includes a powerful `ApiResponse` utility for standardized responses:

```php
use App\Utils\ApiResponse;

// Success response
return ApiResponse::success('Operation completed', ['key' => 'value']);

// Created response (201)
return ApiResponse::created('Resource created', $resource);

// Error responses
return ApiResponse::badRequest('Invalid input');
return ApiResponse::notFound('Resource not found');
return ApiResponse::error('Internal server error');
```

**Available Methods:**
- `success($message, $data = [])` - 200 OK
- `created($message, $data = [])` - 201 Created
- `badRequest($message, $errors = [])` - 400 Bad Request
- `notFound($message)` - 404 Not Found
- `forbidden($message)` - 403 Forbidden
- `validationError($message, $errors)` - 422 Unprocessable Entity
- `error($message)` - 500 Internal Server Error

---

## 🛠️ Advanced Usage

### Manual File Publishing

If you need to publish files manually or check what will be installed:

```bash
# View the stub files
ls vendor/softigital-dev/core/stubs/

# Manually copy if needed (not recommended)
cp vendor/softigital-dev/core/stubs/Utils/ApiResponse.stub app/Utils/ApiResponse.php
```

### Customizing Published Files

All published files are standard PHP classes that you can modify:

```php
// app/Services/AuthService.php
public function register($data)
{
    // Add your custom logic here
    if (User::where('email', $data['email'])->exists()) {
        throw new BadRequestHttpException('Email already exists');
    }

    // Custom user creation logic
    $user = User::create([
        'name' => $data['name'],
        'email' => $data['email'],
        'password' => Hash::make($data['password']),
        // Add custom fields
    ]);

    return $user->refresh();
}
```

### Extending Controllers

```php
// app/Http/Controllers/Auth/AuthController.php
public function logout(Request $request)
{
    $request->user()->currentAccessToken()->delete();

    return ApiResponse::success('Logged out successfully');
}
```

Then add the route:

```php
// routes/v1/auth.php
Route::post('auth/logout', [AuthController::class, 'logout'])
    ->middleware('auth:sanctum');
```

---

## 🔒 Security Considerations

### Token Management

The package uses Laravel Sanctum for token-based authentication:

- Tokens are stored in the `personal_access_tokens` table
- Each login/registration creates a new token
- Tokens don't expire by default (configure in `sanctum.php`)
- Revoke tokens by deleting from the database

### Password Security

- Passwords are hashed using Laravel's default bcrypt
- Minimum 8 characters enforced in validation
- Consider adding password confirmation for sensitive operations

### Google OAuth

- Google ID tokens are verified server-side using Google API Client
- Never trust tokens without verification
- Store `google_id` for account linking
- Handle edge cases (existing email with different auth method)

---

## 🧪 Testing

### Testing Authentication Endpoints

```php
use Tests\TestCase;

class AuthTest extends TestCase
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
                'data' => [
                    'user',
                    'token',
                ],
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
            ->assertJsonStructure([
                'data' => ['token'],
            ]);
    }
}
```

---

## 🤝 Contributing

We welcome contributions! Please follow these guidelines:

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

### Development Setup

```bash
# Clone the repository
git clone https://github.com/softigital-dev/core.git

# Install dependencies
composer install

# Run tests (if available)
composer test
```

---

## 📝 Changelog

### Version 1.0.0 (2026-02-15)

- Initial release
- Authentication system (login, register, user profile)
- Google OAuth integration
- API response utilities
- Automatic route structure setup
- Laravel 11+ support

---

## 📄 License

This package is open-sourced software licensed under the [MIT license](LICENSE).

---

## 👥 Credits

- **Youssef Ehab** - [youssef.ehab@softigital.com](mailto:youssef.ehab@softigital.com)
- **Softigital Team** - [https://softigital.com](https://softigital.com)

---

## 🆘 Support

For issues, questions, or contributions:

- **GitHub Issues**: [https://github.com/softigital-dev/core/issues](https://github.com/softigital-dev/core/issues)
- **Email**: support@softigital.com
- **Documentation**: [https://github.com/softigital-dev/core](https://github.com/softigital-dev/core)

---

## 🎯 Roadmap

Future features planned:

- [ ] Email verification flow
- [ ] Password reset functionality
- [ ] Two-factor authentication (2FA)
- [ ] Social auth (Facebook, GitHub, etc.)
- [ ] Role-based access control (RBAC)
- [ ] API rate limiting utilities
- [ ] Audit logging system

---

<p align="center">Made with ❤️ by <a href="https://softigital.com">Softigital</a></p>
