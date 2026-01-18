# TramitewWeb - Laravel Application

## Project Overview

TramitewWeb is a Laravel 12 web application that appears to be a document management/tracking system (based on the model names like Document, DocumentFile, DocumentType, Movement, Office). The application uses FilamentPHP for its admin panel interface and follows modern Laravel conventions.

### Key Technologies & Features

- **Framework**: Laravel 12 (PHP 8.2+)
- **Admin Panel**: FilamentPHP v4.5+
- **Frontend Build Tool**: Vite with TailwindCSS
- **Icons**: Blade Solar Icons
- **Testing**: PestPHP for testing
- **Code Quality**: Laravel Pint for code formatting
- **Database**: Configured for migrations and Eloquent ORM

### Domain Models

The application manages several key entities:
- **Users**: Authentication and authorization
- **Customers**: Client management
- **Documents**: Core document tracking
- **DocumentFiles**: File attachments
- **DocumentTypes**: Document categorization
- **Offices**: Organizational units
- **Movements**: Document movement tracking
- **Priorities**: Priority levels for documents
- **Administrations**: Administrative entities

### Project Structure

```
app/
├── Enum/           # PHP Enums
├── Filament/       # Filament admin panels
│   ├── Resources/  # Resource definitions
│   └── User/       # User panel customizations
├── Http/           # Controllers, middleware
├── Livewire/       # Livewire components
├── Models/         # Eloquent models
├── Providers/      # Service providers
bootstrap/          # Framework bootstrap
config/             # Configuration files
database/           # Migrations, seeds, factories
public/             # Public assets
resources/          # Views, CSS, JS
routes/             # Route definitions
storage/            # Storage directory
tests/              # Test files
```

## Building and Running

### Prerequisites
- PHP 8.2+
- Composer
- Node.js and npm
- Database (MySQL, PostgreSQL, or SQLite)

### Setup Instructions

1. **Install Dependencies**:
   ```bash
   composer install
   npm install
   ```

2. **Environment Configuration**:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

3. **Database Setup**:
   Configure your database credentials in `.env` and run:
   ```bash
   php artisan migrate
   ```

4. **Build Assets**:
   ```bash
   npm run build
   ```

5. **Run Development Server**:
   ```bash
   # Using the custom dev script with concurrent processes
   composer run dev
   
   # Or traditional Laravel serve
   php artisan serve
   npm run dev
   ```

### Available Scripts

From `composer.json`:
- `composer run setup` - Complete setup including dependencies, env file, key generation, migrations, and asset building
- `composer run dev` - Start development server with concurrent processes (server, queue, logs, vite)
- `composer run test` - Run tests with configuration clearing

From `package.json`:
- `npm run dev` - Start Vite development server
- `npm run build` - Build production assets

### Filament Admin Panel

The application uses FilamentPHP for administration. After installation:
1. Run `php artisan filament:install --panels` (already done based on composer scripts)
2. Access the admin panel at `/admin` (default route)
3. Create a user and assign appropriate roles/permissions

## Development Conventions

### Coding Standards
- Follow PSR-12 coding standards
- Use Laravel Pint for automatic code formatting (`./vendor/bin/pint`)
- Consistent naming conventions for models, controllers, and views

### Testing
- Use PestPHP for testing (configured in composer.json)
- Place tests in the `tests/` directory
- Follow Laravel testing practices with feature and unit tests

### Frontend
- Use TailwindCSS for styling
- Leverage Vite for asset compilation
- Store frontend assets in `resources/css` and `resources/js`

### Model Relationships
- Define relationships in Eloquent models
- Use appropriate foreign keys and constraints
- Implement proper validation rules

## Special Features

### Document Management System
The application appears to be designed as a document management/tracking system with:
- Document lifecycle tracking
- Movement history
- Priority management
- Office-based organization
- File attachment capabilities

### Filament Integration
- Custom admin resources for managing all domain models
- User panel for customer-facing functionality
- Theme customization in `resources/css/filament/`

## Common Commands

```bash
# Artisan commands
php artisan list                    # Show all available commands
php artisan migrate               # Run database migrations
php artisan db:seed              # Seed the database
php artisan cache:clear          # Clear application cache
php artisan config:clear         # Clear configuration cache
php artisan route:list           # List all routes

# Package management
composer install                 # Install PHP dependencies
composer update                  # Update PHP dependencies
composer dump-autoload          # Regenerate autoload files

# Frontend
npm install                      # Install frontend dependencies
npm run dev                     # Start development server
npm run build                   # Build production assets

# Testing
./vendor/bin/pest               # Run tests with Pest
php artisan test                # Alternative way to run tests
```

## Environment Configuration

The application uses standard Laravel environment configuration:
- Copy `.env.example` to `.env` for local development
- Configure database, mail, and other service credentials in `.env`
- Use `php artisan key:generate` to create a unique application key