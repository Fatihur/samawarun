# Database (database/)

## Package Identity

SQLite (development) / MySQL (production) database for Samawa Run. Uses Laravel migrations, seeders, and Eloquent factories.

## Setup & Run

```bash
# Run all migrations
php artisan migrate

# Run specific migration
php artisan migrate --path=database/migrations/2026_03_07_034937_create_events_table.php

# Rollback last batch
php artisan migrate:rollback

# Fresh database with seeders
php artisan migrate:fresh --seed

# Run seeders only
php artisan db:seed
php artisan db:seed --class=EventSeeder
```

## Patterns & Conventions

### Migrations (`database/migrations/`)

**Naming**: `YYYY_MM_DD_HHMMSS_descriptive_action_table.php`

**Standard Structure**:
```php
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->date('date');
            $table->integer('price')->default(0);
            $table->string('status')->default('active');
            $table->timestamps();
            $table->softDeletes();
        });
    }
    
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
```

**DO**:
- Use `timestamps()` on all tables
- Use `softDeletes()` for tables that need soft delete
- Use `nullable()` for optional fields
- Use `default()` for sensible defaults
- Use `unique()` for fields that must be unique
- Use `index()` for frequently queried columns
- Add foreign keys with `constrained()` and `onDelete()`

**Column Types**:
- `string()` - For short text (names, emails, slugs)
- `text()` - For longer text (descriptions)
- `integer()` - For numbers (prices in rupiah)
- `boolean()` - For true/false flags
- `date()` / `datetime()` - For dates and timestamps
- `json()` - For flexible data structures
- `enum()` - For status fields (prefer constants in models)

**Examples**:
- `2026_03_07_034937_create_events_table.php` - Event table with slug, dates
- `2026_03_07_034937_create_participants_table.php` - Complex workflow statuses
- `2026_03_07_160208_create_distance_categories_table.php` - Categories with relationship

### Alter Migrations

**Adding Columns**:
```php
public function up(): void
{
    Schema::table('events', function (Blueprint $table) {
        $table->date('registration_deadline')->nullable()->after('date');
        $table->boolean('is_race_event')->default(false)->after('registration_deadline');
    });
}

public function down(): void
{
    Schema::table('events', function (Blueprint $table) {
        $table->dropColumn(['registration_deadline', 'is_race_event']);
    });
}
```

**Examples**:
- `2026_03_08_130000_add_registration_deadline_to_events_table.php`
- `2026_03_14_152405_add_kiosk_logo_settings_to_bib_settings_table.php`

### Pivot Tables (Many-to-Many)

**Naming**: `table1_table2` in alphabetical order

```php
Schema::create('distance_category_event', function (Blueprint $table) {
    $table->id();
    $table->foreignId('distance_category_id')->constrained()->onDelete('cascade');
    $table->foreignId('event_id')->constrained()->onDelete('cascade');
    $table->integer('price')->default(0);  // Additional pivot data
    $table->timestamps();
});
```

**Example**: `2026_03_07_160211_create_distance_category_event_table.php`

### Seeders (`database/seeders/`)

**Naming**: PascalCase with Seeder suffix (e.g., `EventSeeder.php`)

**Standard Pattern**:
```php
class EventSeeder extends Seeder
{
    public function run(): void
    {
        Event::factory()->count(10)->create();
        
        // Or specific data
        Event::create([
            'name' => 'Samawa Run 2024',
            'date' => '2024-12-25',
            'price' => 150000,
        ]);
    }
}
```

**Main Seeder**: `DatabaseSeeder.php` calls all other seeders

**Seeders Available**:
- `DatabaseSeeder.php` - Main seeder
- `EventSeeder.php` - Sample events
- `ParticipantSeeder.php` - Sample participants
- `DistanceCategorySeeder.php` - 5K, 7K, 10K categories
- `BibSettingSeeder.php` - Default BIB settings

### Factories (`database/factories/`)

**Naming**: PascalCase with Factory suffix (e.g., `EventFactory.php`)

**Pattern**:
```php
class EventFactory extends Factory
{
    protected $model = Event::class;
    
    public function definition(): array
    {
        return [
            'name' => fake()->sentence(3),
            'slug' => fake()->slug(),
            'description' => fake()->paragraph(),
            'date' => fake()->dateTimeBetween('+1 week', '+1 year'),
            'price' => fake()->randomElement([100000, 150000, 200000]),
        ];
    }
}
```

## Core Schema

### Events Table
- Stores running events
- Has slug for SEO-friendly URLs
- Supports registration deadline
- Supports race vs fun-run distinction

### Participants Table
- Complex workflow status tracking
- BIB number generation
- Payment token for secure URLs
- Emergency contact fields
- Distance category relationship

### Distance Categories
- 5K, 7K, 10K categories
- Many-to-many with events via pivot table with pricing

### BIB Settings
- Template configuration
- Background image path
- Font settings
- Layout coordinates

### Certificate Templates
- Visual editor JSON storage
- Text element positions
- Background image

## JIT Index Hints

```bash
# Find migration for specific table
rg -n "Schema::create.*events" database/migrations/

# Find all create table migrations
rg -n "Schema::create" database/migrations/

# Find migrations that add columns
rg -n "Schema::table" database/migrations/

# List all seeders
ls database/seeders/

# List all factories
ls database/factories/

# Check migration status
php artisan migrate:status
```

## Common Gotchas

- **Order matters** - Migrations run in filename order, ensure foreign key tables exist first
- **Soft deletes** - Use `softDeletes()` on tables where data might need recovery
- **JSON columns** - Use for flexible data like certificate visual editor data
- **Price storage** - Store in integer (rupiah), display with number_format
- **Date formats** - Always use `date()` or `datetime()`, not strings
- **Foreign keys** - Use `constrained()` for automatic foreign key naming
- **Index performance** - Add indexes on columns used in WHERE clauses

## Database Relationships

```
users (1) ----< participants (many)
events (1) ----< participants (many)
events (many) ----< distance_categories (many) via distance_category_event
participants (1) ----< bib_settings (1) via bib_number
events (1) ----< galleries (many)
```

## Pre-PR Checks

```bash
# Run migrations on fresh database
php artisan migrate:fresh

# Check for migration errors
php artisan migrate --pretend

# Verify seeder works
php artisan db:seed --class=DatabaseSeeder
```
