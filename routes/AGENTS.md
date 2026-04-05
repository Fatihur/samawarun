# Routes (routes/)

## Package Identity

Laravel route definitions using closure and controller-based routes. All web routes defined in `web.php`, console commands in `console.php`.

## Setup & Run

```bash
# List all routes
php artisan route:list

# List routes with verbose info
php artisan route:list -v

# Check for route errors
php artisan route:cache

# Clear route cache
php artisan route:clear
```

## Patterns & Conventions

### Route File Structure

**Web Routes** (`routes/web.php`):
- Public routes at root level
- Admin routes under `/admin` prefix with middleware
- Named routes using `route('name')` convention

**Console Routes** (`routes/console.php`):
- Artisan command definitions
- Scheduled tasks

### Route Groups

**Admin Routes**:
```php
Route::prefix('admin')->name('admin.')->group(function (): void {
    // Auth routes (no middleware)
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    
    // Protected routes
    Route::middleware(['auth', 'admin'])->group(function (): void {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        
        // Resource routes
        Route::resource('events', EventController::class);
        Route::resource('participants', ParticipantController::class);
    });
});
```

**Public Routes**:
```php
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/events', [PublicEventController::class, 'index'])->name('events.index');
Route::get('/events/{slug}', [PublicEventController::class, 'show'])->name('events.show');
```

### Resource Routes

**Standard RESTful Routes**:
```php
Route::resource('events', EventController::class);
```

**Generates**:
- GET `/admin/events` → index
- GET `/admin/events/create` → create
- POST `/admin/events` → store
- GET `/admin/events/{id}` → show
- GET `/admin/events/{id}/edit` → edit
- PUT/PATCH `/admin/events/{id}` → update
- DELETE `/admin/events/{id}` → destroy

**Custom Resource Options**:
```php
// Exclude specific routes
Route::resource('events', EventController::class)->except(['show']);

// Only specific routes
Route::resource('events', EventController::class)->only(['index', 'store']);

# API Resource (no create/edit forms)
Route::apiResource('events', ApiEventController::class);
```

### Named Routes

**Convention**: `prefix.action` format

```php
// Admin routes
Route::get('/admin/events', [EventController::class, 'index'])->name('admin.events.index');
Route::get('/admin/events/{event}/edit', [EventController::class, 'edit'])->name('admin.events.edit');

// Public routes
Route::get('/events', [EventController::class, 'index'])->name('events.index');
```

**Usage in Views**:
```blade
<a href="{{ route('admin.events.index') }}">Events</a>
<a href="{{ route('admin.events.edit', $event) }}">Edit</a>
```

### Route Parameters

**Required Parameters**:
```php
Route::get('/events/{slug}', [EventController::class, 'show']);
```

**Optional Parameters**:
```php
Route::get('/events/{slug?}', [EventController::class, 'show']);
```

**Parameter Constraints**:
```php
Route::get('/events/{id}', [EventController::class, 'show'])
    ->where('id', '[0-9]+');

Route::get('/events/{slug}', [EventController::class, 'show'])
    ->where('slug', '[a-z0-9-]+');
```

### DataTable Routes

**AJAX Endpoints for DataTables**:
```php
Route::get('/admin/events/data', [EventController::class, 'data'])
    ->name('admin.events.data');
```

**Controller Implementation**:
```php
public function data(Request $request): JsonResponse
{
    $events = Event::select(['id', 'name', 'date', 'status']);
    
    return DataTables::of($events)
        ->addColumn('action', function ($event) {
            return view('admin.events.action', compact('event'))->render();
        })
        ->rawColumns(['action'])
        ->make(true);
}
```

### Custom Actions

**Beyond Resource Routes**:
```php
// Verify participant
Route::post('/admin/participants/{participant}/verify', 
    [ParticipantController::class, 'verify'])
    ->name('admin.participants.verify');

// Export participants
Route::get('/admin/participants/export', 
    [ParticipantController::class, 'export'])
    ->name('admin.participants.export');

// Payment with token
Route::get('/payment/{token}', 
    [PaymentController::class, 'show'])
    ->name('payment.show');
```

### Middleware

**Route-Level Middleware**:
```php
Route::get('/admin/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'admin'])
    ->name('admin.dashboard');
```

**Group Middleware**:
```php
Route::middleware(['auth', 'admin'])->group(function (): void {
    Route::resource('events', EventController::class);
    Route::resource('participants', ParticipantController::class);
});
```

**Middleware Aliases**:
- `auth` - Laravel authentication
- `admin` - Custom admin check (EnsureAdmin middleware)

### Route Model Binding

**Implicit Binding** (Laravel auto-resolves):
```php
Route::get('/admin/events/{event}/edit', [EventController::class, 'edit']);
```

Controller receives Event model:
```php
public function edit(Event $event): View
{
    return view('admin.events.edit', compact('event'));
}
```

**Custom Key**:
```php
Route::get('/events/{event:slug}', [EventController::class, 'show']);
```

## Key Route Patterns

### Registration Flow Routes

```
/events/{slug}/register     → Registration form
POST /registrations         → Submit registration
/payment/{token}            → Payment page
POST /payment/{token}       → Submit payment
```

### Admin Routes Structure

```
/admin/dashboard              → Dashboard
/admin/events                 → Event list
/admin/events/create          → Create event
/admin/events/{id}/edit       → Edit event
/admin/participants           → Participant list
/admin/participants/{id}      → Participant detail
/admin/bib-settings           → BIB configuration
/admin/certificates           → Certificate templates
/admin/race-timing           → Race timing/stopwatch
/admin/race-reports          → Race reports
/admin/gallery              → Photo gallery
```

## JIT Index Hints

```bash
# Search for all GET routes
rg -n "Route::get" routes/

# Search for all POST routes
rg -n "Route::post" routes/

# Search for all resource routes
rg -n "Route::resource" routes/

# Search for named routes
rg -n "->name\(" routes/

# Search for middleware
rg -n "->middleware\(" routes/

# Find routes with specific controller
rg -n "EventController" routes/

# List all routes in order
php artisan route:list --columns=method,uri,name,action | head -50
```

## Common Gotchas

- **Order matters** - More specific routes before generic ones
- **Named routes** - Always use `->name()` for routes referenced in code
- **Resource routes** - Remember they create multiple routes automatically
- **Middleware** - Apply to groups when multiple routes need same protection
- **Route parameters** - Use type hints in controller for automatic model binding
- **DataTable routes** - Must return JsonResponse, not views

## Route Testing

```bash
# Cache routes for performance
php artisan route:cache

# Test specific route
php artisan route:list --name=admin.events.index

# Check route action
php artisan route:list --path=admin/events
```

## Pre-PR Checks

```bash
# Verify routes compile
php artisan route:cache

# Check for route errors
php artisan route:list | grep -i error

# Clear cache after testing
php artisan route:clear
```
