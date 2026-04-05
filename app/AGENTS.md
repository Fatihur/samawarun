# Application Code (app/)

## Package Identity

Laravel 11 application code with MVC architecture. Supports running event management workflows, PDF generation, QR/barcodes, and AI integrations.

## Key Dependencies

- **Laravel 11** - Core framework
- **Livewire** - Reactive UI components
- **DomPDF** - PDF generation for certificates/BIBs
- **OpenAI** - AI integration
- **DataTables** - Admin table components
- **Blade Heroicons** - Icon components

## Setup & Run

```bash
# No separate build - uses Laravel's artisan
php artisan serve        # Start dev server
php artisan route:list   # List all routes
php artisan tinker       # Interactive shell
```

## Patterns & Conventions

### Models (`app/Models/`)

**Naming**: Singular PascalCase (e.g., `Event.php`, `Participant.php`)

**Standard Structure**:
```php
class Participant extends Model
{
    // Constants for enums
    public const STATUS_PENDING = 'pending';
    public const STATUS_VERIFIED = 'verified';
    
    // Fillable fields
    protected $fillable = ['name', 'email', 'status'];
    
    // Casts
    protected function casts(): array
    {
        return [
            'registration_date' => 'datetime',
        ];
    }
    
    // Accessors
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'Menunggu',
            self::STATUS_VERIFIED => 'Terverifikasi',
            default => 'Unknown',
        };
    }
    
    // Relationships
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }
}
```

**DO**:
- Use constants for enum values: `Event::STATUS_PENDING`
- Use typed properties and return types
- Use `$table` property if table name differs from pluralized model name
- Use `belongsToMany` for many-to-many relationships
- Add `boot()` method for auto-generating slugs

**Examples**:
- Event model: `app/Models/Event.php` - slug auto-generation, price formatting
- Participant model: `app/Models/Participant.php` - workflow status constants, BIB generation

### Controllers (`app/Http/Controllers/`)

**Organization**:
- Admin controllers: `app/Http/Controllers/Admin/`
- Public controllers: `app/Http/Controllers/Public/`

**Naming**: PascalCase with Controller suffix (e.g., `EventController.php`)

**DO**:
- Use `__invoke()` for single-action controllers
- Use standard resource methods (index, create, store, edit, update, destroy)
- Return named routes with `route('admin.events.index')`
- Use DataTables for admin index pages with AJAX
- Use `validate()` for form validation

**DON'T**:
- Put business logic in controllers - move to Services
- Use raw queries - use Eloquent or Query Builder

**Example Pattern**:
```php
class EventController extends Controller
{
    public function index(): View
    {
        return view('admin.events.index');
    }
    
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'date' => 'required|date',
        ]);
        
        Event::create($validated);
        
        return redirect()->route('admin.events.index')
            ->with('success', 'Event berhasil dibuat');
    }
}
```

**Key Controllers**:
- Dashboard: `Admin/DashboardController.php` - Analytics and charts
- Participants: `Admin/ParticipantController.php` - Workflow management, exports
- Certificates: `Admin/CertificateController.php` - PDF generation with visual editor
- Registration: `Public/RegistrationController.php` - Multi-step registration flow

### Services (`app/Services/`)

**Naming**: PascalCase with Service suffix (e.g., `CertificateTemplateService.php`)

**Purpose**: Extract complex business logic, especially PDF generation, external API calls

**DO**:
- Use dependency injection in constructor
- Return DTOs or simple arrays
- Handle errors gracefully with try/catch

**Example**: `CertificateTemplateService.php`:
- Custom font loading from `storage/fonts/`
- Image compression for backgrounds
- Visual editor element rendering (text, images, rectangles)

### Middleware (`app/Http/Middleware/`)

**Naming**: PascalCase with descriptive names (e.g., `EnsureAdmin.php`)

**Pattern**: Check `is_admin` flag on User model

**Example**: `EnsureAdmin.php` - Redirects non-admin users to login

### Notifications (`app/Notifications/`)

**Naming**: PascalCase with Notification suffix (e.g., `NewParticipantNotification.php`)

**Purpose**: Email notifications for registration workflow

**DO**:
- Use `toMail()` method for email content
- Use Indonesian language for email subject/body
- Include relevant data in constructor

**Examples**:
- `NewParticipantNotification.php` - Sent to admin when new registration
- `ParticipantVerifiedNotification.php` - Sent to participant when approved

### Providers (`app/Providers/`)

- `AppServiceProvider.php` - Standard Laravel provider
- `AiServiceProvider.php` - Registers OpenAI/Cliprox client in container

## Key Files

- **Auth**: `app/Http/Controllers/Admin/AuthController.php`
- **Workflow**: `app/Models/Participant.php` - Registration status management
- **PDF Service**: `app/Services/CertificateTemplateService.php`
- **Admin Middleware**: `app/Http/Middleware/EnsureAdmin.php`
- **User Model**: `app/Models/User.php` - Admin flag, notifications

## Registration Workflow

Participant goes through 6 statuses:
1. `pending` - Initial submission
2. `registration_reviewed` - Admin reviewed registration
3. `approved_waiting_payment` - Approved, waiting for payment
4. `payment_submitted` - Payment proof uploaded
5. `payment_reviewed` - Admin reviewed payment
6. `verified` - Completed

**Status Constants** in `Participant.php`:
```php
public const STATUS_PENDING = 'pending';
public const STATUS_REGISTRATION_REVIEWED = 'registration_reviewed';
public const STATUS_APPROVED_WAITING_PAYMENT = 'approved_waiting_payment';
public const STATUS_PAYMENT_SUBMITTED = 'payment_submitted';
public const STATUS_PAYMENT_REVIEWED = 'payment_reviewed';
public const STATUS_VERIFIED = 'verified';
```

## JIT Index Hints

```bash
# Find all models
rg -n "^class.*extends Model" app/Models/

# Find all controllers
rg -n "^class.*Controller extends Controller" app/Http/Controllers/

# Find a specific method
rg -n "function methodName" app/

# Find DataTable endpoints
rg -n "Route::get.*data" routes/

# Find notification classes
rg -n "^class.*Notification" app/Notifications/
```

## Common Gotchas

- **Indonesian only** - All user-facing text must be in Indonesian
- **Admin middleware** - All admin routes need `auth` + `admin` middleware
- **BIB generation** - Auto-generated as `[distance]000[sequence]` (e.g., 5001)
- **Payment tokens** - Use secure tokens with expiration for payment URLs
- **Custom fonts** - Store in `storage/fonts/` for PDF generation
- **Image uploads** - Use `storage/app/public/` and create symlinks

## Pre-PR Checks

```bash
php artisan test && php -l app/ && php artisan route:list | grep -i error
```
