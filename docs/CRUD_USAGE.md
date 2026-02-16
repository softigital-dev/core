# CRUD Command Usage Guide

This guide demonstrates how to use the `make:crud` command to quickly scaffold a complete RESTful API.

## Quick Example: Blog Posts API

Let's create a complete blog posts API from scratch:

### Step 1: Generate CRUD Structure

```bash
php artisan make:crud Post
```

This creates all necessary files:
```
✓ Model created
✓ Migration created
✓ Service created
✓ Controller created
✓ Store Request created
✓ Update Request created
✓ Resource created
✓ Routes created
```

### Step 2: Define Model Properties

Edit `app/Models/Post.php`:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'content',
        'excerpt',
        'status',
        'published_at',
    ];

    protected $casts = [
        'published_at' => 'datetime',
    ];
}
```

### Step 3: Define Migration Schema

Edit `database/migrations/xxxx_xx_xx_xxxxxx_create_posts_table.php`:

```php
Schema::create('posts', function (Blueprint $table) {
    $table->id();
    $table->string('title');
    $table->string('slug')->unique();
    $table->text('content');
    $table->text('excerpt')->nullable();
    $table->enum('status', ['draft', 'published'])->default('draft');
    $table->timestamp('published_at')->nullable();
    $table->timestamps();
});
```

### Step 4: Add Validation Rules

Edit `app/Http/Requests/StorePostRequest.php`:

```php
public function rules(): array
{
    return [
        'title' => 'required|string|max:255',
        'slug' => 'required|string|unique:posts,slug|max:255',
        'content' => 'required|string',
        'excerpt' => 'nullable|string',
        'status' => 'required|in:draft,published',
        'published_at' => 'nullable|date',
    ];
}
```

Edit `app/Http/Requests/UpdatePostRequest.php`:

```php
public function rules(): array
{
    return [
        'title' => 'sometimes|required|string|max:255',
        'slug' => 'sometimes|required|string|unique:posts,slug,' . $this->route('id') . '|max:255',
        'content' => 'sometimes|required|string',
        'excerpt' => 'nullable|string',
        'status' => 'sometimes|required|in:draft,published',
        'published_at' => 'nullable|date',
    ];
}
```

### Step 5: Define Resource Response

Edit `app/Http/Resources/PostResource.php`:

```php
public function toArray(Request $request): array
{
    return [
        'id' => $this->id,
        'title' => $this->title,
        'slug' => $this->slug,
        'content' => $this->content,
        'excerpt' => $this->excerpt,
        'status' => $this->status,
        'published_at' => $this->published_at?->toIso8601String(),
        'created_at' => $this->created_at->toIso8601String(),
        'updated_at' => $this->updated_at->toIso8601String(),
    ];
}
```

### Step 6: Register Routes

Edit `routes/api.php` and add:

```php
require __DIR__.'/v1/posts.php';
```

### Step 7: Run Migration

```bash
php artisan migrate
```

## Your API is Ready! 🎉

You now have a complete RESTful API with the following endpoints:

### List Posts (Paginated)
```bash
GET /api/v1/posts?per_page=10
Authorization: Bearer {token}
```

**Response:**
```json
{
  "status": 200,
  "message": "Posts retrieved successfully",
  "meta": {
    "current_page": 1,
    "per_page": 10,
    "total": 25,
    "last_page": 3
  },
  "data": {
    "items": [
      {
        "id": 1,
        "title": "My First Post",
        "slug": "my-first-post",
        "content": "Post content here...",
        "excerpt": "Brief summary",
        "status": "published",
        "published_at": "2025-01-15T10:30:00Z",
        "created_at": "2025-01-15T10:00:00Z",
        "updated_at": "2025-01-15T10:30:00Z"
      }
    ]
  }
}
```

### Get Single Post
```bash
GET /api/v1/posts/1
Authorization: Bearer {token}
```

### Create Post
```bash
POST /api/v1/posts
Authorization: Bearer {token}
Content-Type: application/json

{
  "title": "My New Post",
  "slug": "my-new-post",
  "content": "This is the post content...",
  "excerpt": "A brief summary",
  "status": "draft",
  "published_at": null
}
```

### Update Post
```bash
PATCH /api/v1/posts/1
Authorization: Bearer {token}
Content-Type: application/json

{
  "status": "published",
  "published_at": "2025-01-20T12:00:00Z"
}
```

### Delete Post
```bash
DELETE /api/v1/posts/1
Authorization: Bearer {token}
```

## Understanding the Generated Code

### Controller

The controller uses:
- **Service layer** for business logic separation
- **Request validation** for data validation
- **Resources** for consistent response formatting
- **ApiResponse utility** for standardized JSON responses

### Service

The service provides clean CRUD methods:
- `getAll($perPage)` - Paginated list
- `getById($id)` - Find single record
- `create($data)` - Create new record
- `update($model, $data)` - Update existing record
- `delete($model)` - Delete record

### Pagination Handling

The `index()` method properly handles pagination:

```php
// Service returns LengthAwarePaginator
$posts = $this->service->getAll(request('per_page', 15));

// Controller manually builds response with metadata
return ApiResponse::success(
    message: 'Posts retrieved successfully',
    data: [
        'meta' => [
            'current_page' => $posts->currentPage(),
            'per_page' => $posts->perPage(),
            'total' => $posts->total(),
            'last_page' => $posts->lastPage(),
        ],
        'items' => PostResource::collection($posts->items()),
    ],
    skipPrepare: true  // Important: skip prepareData since we built it manually
);
```

### Non-Paginated Resource

For single resources (show, store, update):

```php
return ApiResponse::success(
    message: 'Post retrieved successfully',
    data: new PostResource($post)
);
```

The `ApiResponse::prepareData()` method automatically wraps it in the correct format.

## Customization Tips

### Adding Relationships

In your service, you can eager load relationships:

```php
public function getAll(int $perPage = 15): LengthAwarePaginator
{
    return Post::with(['author', 'tags'])->paginate($perPage);
}
```

### Adding Search/Filters

Extend the service method:

```php
public function getAll(int $perPage = 15, array $filters = []): LengthAwarePaginator
{
    $query = Post::query();
    
    if (isset($filters['status'])) {
        $query->where('status', $filters['status']);
    }
    
    if (isset($filters['search'])) {
        $query->where('title', 'like', "%{$filters['search']}%");
    }
    
    return $query->paginate($perPage);
}
```

Then in controller:

```php
public function index()
{
    $filters = request()->only(['status', 'search']);
    $posts = $this->service->getAll(request('per_page', 15), $filters);
    // ... rest of the code
}
```

### Custom Business Logic

Add custom methods to your service:

```php
public function publish(Post $post): Post
{
    $post->update([
        'status' => 'published',
        'published_at' => now(),
    ]);
    
    return $post->refresh();
}
```

## Advanced Example: E-commerce Product

```bash
php artisan make:crud Product
```

Then customize for e-commerce needs with categories, pricing, inventory, etc.

---

**That's it!** The `make:crud` command gives you a production-ready starting point that follows Laravel best practices and integrates seamlessly with the Softigital Core package utilities.
