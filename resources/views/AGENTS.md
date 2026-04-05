# Views (resources/views/)

## Package Identity

Blade templates for Samawa Run frontend. Uses layout inheritance, Alpine.js for reactivity, and Heroicons for icons. All user-facing text is in Indonesian.

## Setup & Run

```bash
# No separate build for views
php artisan view:clear    # Clear view cache if needed
php artisan view:cache    # Cache views for production
```

## Patterns & Conventions

### Layout System

**Base Layouts**:
- `resources/views/layouts/admin.blade.php` - Admin dashboard layout
- `resources/views/layouts/public.blade.php` - Public website layout

**Layout Structure**:
```blade
@extends('layouts.admin')

@section('content')
    <div class="container mx-auto">
        <h1>Page Title</h1>
        <!-- Content -->
    </div>
@endsection
```

**DO**:
- Always extend a layout
- Use `@section('content')` for main content
- Use `@push('scripts')` for page-specific JavaScript
- Use `@section('title', 'Page Title')` for page titles

### View Organization

```
resources/views/
├── layouts/
│   ├── admin.blade.php          # Admin base layout
│   ├── public.blade.php         # Public base layout
│   ├── admin/
│   │   ├── sidebar.blade.php    # Admin sidebar navigation
│   │   └── navbar.blade.php     # Admin top navbar
│   └── public/
│       ├── header.blade.php     # Public header
│       └── footer.blade.php     # Public footer
├── admin/                        # Admin views
│   ├── dashboard.blade.php
│   ├── events/
│   ├── participants/
│   └── ...
├── public/                       # Public views
│   ├── home.blade.php
│   ├── events/
│   └── registrations/
├── emails/                       # Email templates
└── pdf/                          # PDF templates
```

### Blade Components

**Icons** (via blade-ui-kit/blade-heroicons):
```blade
<x-heroicon-s-home class="w-5 h-5" />
<x-heroicon-o-user class="w-6 h-6" />
```

**Prefix convention**:
- `s-` = solid icons
- `o-` = outline icons

### Form Patterns

**Standard Form**:
```blade
<form action="{{ route('admin.events.store') }}" method="POST" enctype="multipart/form-data">
    @csrf
    
    <div class="mb-4">
        <label for="name" class="block text-sm font-medium text-gray-700">
            Nama Event
        </label>
        <input type="text" name="name" id="name" 
               value="{{ old('name') }}"
               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
               required>
        @error('name')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>
    
    <button type="submit" class="btn btn-primary">
        Simpan
    </button>
</form>
```

**DO**:
- Use `@csrf` on all POST forms
- Use `old('field')` to preserve input on validation errors
- Use `@error('field')` to display validation messages
- Use Indonesian labels and button text

### Alpine.js Patterns

**Sidebar Toggle**:
```blade
<div x-data="{ sidebarOpen: false }">
    <button @click="sidebarOpen = !sidebarOpen">
        <x-heroicon-s-bars-3 class="w-6 h-6" />
    </button>
    
    <div x-show="sidebarOpen" 
         x-transition:enter="transition ease-out duration-300"
         class="fixed inset-0 z-50">
        <!-- Sidebar content -->
    </div>
</div>
```

**Dropdown**:
```blade
<div x-data="{ open: false }" @click.away="open = false">
    <button @click="open = !open">Menu</button>
    <div x-show="open" class="absolute mt-2 w-48 rounded-md shadow-lg">
        <!-- Dropdown items -->
    </div>
</div>
```

### DataTables Integration

**Admin Index Pages** use DataTables for AJAX loading:

```blade
@extends('layouts.admin')

@section('content')
    <table id="events-table" class="w-full">
        <thead>
            <tr>
                <th>Nama</th>
                <th>Tanggal</th>
                <th>Aksi</th>
            </tr>
        </thead>
    </table>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            $('#events-table').DataTable({
                ajax: '{{ route('admin.events.data') }}',
                columns: [
                    { data: 'name' },
                    { data: 'date' },
                    { data: 'action', orderable: false }
                ]
            });
        });
    </script>
@endpush
```

### PDF Templates (`resources/views/pdf/`)

**Certificate Template**:
- Uses DomPDF for rendering
- Supports custom fonts from `storage/fonts/`
- Position elements with absolute positioning

```blade
<!DOCTYPE html>
<html>
<head>
    <style>
        @font-face {
            font-family: 'CustomFont';
            src: url('{{ storage_path('fonts/custom-font.ttf') }}');
        }
        body { font-family: 'CustomFont', sans-serif; }
    </style>
</head>
<body>
    <div style="position: absolute; top: 200px; left: 100px;">
        {{ $participant->name }}
    </div>
</body>
</html>
```

### Email Templates (`resources/views/emails/`)

**Notification Email**:
```blade
@component('mail::message')
# Halo {{ $participant->name }},

Pendaftaran Anda untuk event {{ $event->name }} telah diverifikasi.

@component('mail::button', ['url' => $url])
Lihat Detail
@endcomponent

Terima kasih,<br>
{{ config('app.name') }}
@endcomponent
```

## Key Files

- **Admin Layout**: `resources/views/layouts/admin.blade.php`
- **Public Layout**: `resources/views/layouts/public.blade.php`
- **Dashboard**: `resources/views/admin/dashboard.blade.php`
- **Event Form**: `resources/views/admin/events/_form.blade.php` - Reusable form partial
- **Registration**: `resources/views/public/registrations/create.blade.php`

## JIT Index Hints

```bash
# Find all blade files
find resources/views -name "*.blade.php"

# Find forms using a specific field
rg -n "name=\"email\"" resources/views/

# Find DataTable tables
rg -n "DataTable" resources/views/

# Find Alpine.js components
rg -n "x-data" resources/views/

# Find icon usage
rg -n "heroicon" resources/views/
```

## Common Gotchas

- **Indonesian only** - All labels, buttons, and messages must be in Indonesian
- **@csrf required** - Always include on POST forms
- **enctype** - Use `enctype="multipart/form-data"` for file uploads
- **Storage paths** - Use `storage_path()` helper for PDF resources, not public paths
- **Old input** - Always use `old('field')` to preserve form state on errors
- **Scripts** - Use `@push('scripts')` for page-specific JS, don't inline in content

## Pre-PR Checks

```bash
# Check for syntax errors in blade files
php artisan view:cache

# Clear cache if needed
php artisan view:clear
```
