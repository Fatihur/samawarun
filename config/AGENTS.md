# Configuration (config/)

## Package Identity

Laravel configuration files plus custom configs for AI integration, PDF generation, and Word templates.

## Setup & Run

```bash
# Cache configs for production
php artisan config:cache

# Clear config cache
php artisan config:clear

# Show specific config
php artisan config:show ai

# Show all config
php artisan config:publish --all
```

## Patterns & Conventions

### Standard Laravel Configs

**Core Configuration Files**:
- `app.php` - Application name, timezone, locale
- `auth.php` - Authentication guards, providers
- `database.php` - Database connections
- `filesystems.php` - Storage disks
- `mail.php` - Mail drivers and settings
- `services.php` - Third-party services

**Usage**:
```php
// In PHP code
config('app.name');
config('database.default');
config('filesystems.disks.public');

// In Blade views
{{ config('app.name') }}
```

### Custom Configs

**AI Configuration** (`config/ai.php`):
```php
return [
    'openai' => [
        'api_key' => env('OPENAI_API_KEY'),
        'organization' => env('OPENAI_ORGANIZATION'),
    ],
    'cliprox' => [
        'api_key' => env('CLIPROX_API_KEY'),
        'base_url' => env('CLIPROX_BASE_URL'),
    ],
];
```

**Environment Variables Required**:
```env
OPENAI_API_KEY=sk-...
OPENAI_ORGANIZATION=org-...
CLIPROX_API_KEY=...
CLIPROX_BASE_URL=https://...
```

**DomPDF Configuration** (`config/dompdf.php`):
- PDF generation settings
- Font directories
- Paper size defaults

**Word Template Configuration** (`config/word.php`):
- Template paths
- Default formatting

### Environment Configuration

**`.env` Structure**:
```env
# Application
APP_NAME="Samawa Run"
APP_ENV=local
APP_KEY=base64:...
APP_DEBUG=true
APP_URL=http://localhost

# Database
DB_CONNECTION=sqlite
DB_DATABASE=database/database.sqlite

# Mail (for notifications)
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=...
MAIL_PASSWORD=...
MAIL_ENCRYPTION=tls

# AI Services
OPENAI_API_KEY=sk-...

# Filesystem
FILESYSTEM_DISK=public
```

**`.env.example`** - Template for new installations

### Accessing Config Values

**In Controllers**:
```php
use Illuminate\Support\Facades\Config;

$apiKey = Config::get('ai.openai.api_key');
// or
$apiKey = config('ai.openai.api_key');
```

**In Views**:
```blade
{{ config('app.name') }}
@if(config('app.debug'))
    <div>Debug mode active</div>
@endif
```

**Default Values**:
```php
$value = config('ai.openai.api_key', 'default-value');
```

## Key Configurations

### Database Config

**Connection Options**:
- SQLite (default for development)
- MySQL (production)
- PostgreSQL (alternative)

**Example MySQL config**:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=samawarun
DB_USERNAME=root
DB_PASSWORD=secret
```

### Filesystem Config

**Disks**:
- `local` - Private storage
- `public` - Publicly accessible (symlinked)
- `s3` - AWS S3 (optional)

**Usage**:
```php
// Store file
Storage::disk('public')->put('certificates/' . $filename, $pdf);

// Get URL
$url = Storage::disk('public')->url('certificates/' . $filename);
```

### Mail Config

**Drivers**:
- `smtp` - Gmail, custom SMTP
- `mailgun` - Mailgun service
- `ses` - Amazon SES

**For Event Notifications**:
```php
Mail::to($participant->email)
    ->send(new ParticipantVerifiedNotification($participant));
```

## Security & Secrets

**Never commit these to Git**:
- `.env` file
- API keys (OpenAI, Mail, etc.)
- Database credentials
- Encryption keys

**Safe to commit**:
- `config/*.php` (use env() helper)
- `.env.example` (with dummy values)

## JIT Index Hints

```bash
# Show specific config
php artisan config:show database

# Search config files
rg -n "OPENAI" config/
rg -n "MAIL" config/

# Find env() usage
rg -n "env\(" config/

# List all config files
ls config/
```

## Common Gotchas

- **Cache configs in production** - Run `php artisan config:cache`
- **Never commit .env** - Add to .gitignore
- **Use env() only in config files** - Not in controllers or views
- **Default values** - Always provide sensible defaults in config files
- **Type casting** - env() returns strings, cast booleans/numerics as needed

## Pre-PR Checks

```bash
# Verify configs load
php artisan config:cache

# Check for syntax errors
php -l config/*.php

# Clear cache after testing
php artisan config:clear
```
