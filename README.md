# Laravel Starter Template

A comprehensive Laravel 11 starter template with Docker, authentication, health endpoints, optimized logging, and SQLite out of the box.

**Created by [C-Spydo](https://github.com/C-Spydo)**  
**Contact: csamsonok@gmail.com**

## Features

- 🐳 **Dockerized** - Complete Docker setup with Nginx and PHP-FPM 8.2
- 🔐 **Authentication Ready** - Laravel Sanctum authentication with API tokens
- 🏥 **Health Endpoints** - Built-in health check endpoints for monitoring
- 📝 **Optimized Logging** - Structured logging with proper configuration
- 💾 **SQLite by Default** - Lightweight database setup for rapid development
- 🚀 **Production Ready** - Optimized for both development and production
- 🧪 **Testing Setup** - PHPUnit configuration with example tests
- 📊 **API Documentation** - Automatic Swagger/OpenAPI documentation
- ⚡ **Modern Frontend** - Vite build tool with Tailwind CSS

## Quick Start

### Prerequisites

- Docker and Docker Compose
- Git

### Installation

1. **Clone the repository**
   ```bash
   git clone <your-repo-url>
   cd laravel-starter-template
   ```

2. **Copy environment file**
   ```bash
   cp .env.example .env
   ```

3. **Start the application**
   ```bash
   docker-compose up -d
   ```

4. **Install dependencies and setup**
   ```bash
   docker-compose exec app composer install
   docker-compose exec app php artisan key:generate
   docker-compose exec app php artisan migrate
   docker-compose exec app php artisan db:seed
   ```

5. **Setup Swagger documentation**
   ```bash
   # Publish Swagger assets
   docker-compose exec app php artisan vendor:publish --provider "L5Swagger\L5SwaggerServiceProvider"
   
   # Generate API documentation
   docker-compose exec app php artisan l5-swagger:generate
   ```

6. **Access your application**
   - Web: http://localhost:8000
   - API: http://localhost:8000/api
   - Health Check: http://localhost:8000/health
   - **API Documentation**: http://localhost:8000/api/documentation

## Troubleshooting

### If Docker build fails with autoloader errors

If you encounter issues during the Docker build process, you can manually install dependencies:

```bash
# Start containers without building
docker-compose up -d

# Install dependencies manually
docker-compose exec app composer install

# Generate application key
docker-compose exec app php artisan key:generate

# Run migrations (will create SQLite database if it doesn't exist)
docker-compose exec app php artisan migrate

# Seed the database
docker-compose exec app php artisan db:seed

# Setup Swagger documentation
docker-compose exec app php artisan vendor:publish --provider "L5Swagger\L5SwaggerServiceProvider"
docker-compose exec app php artisan l5-swagger:generate
```

### Common Issues

- **Autoloader not found**: Run `docker-compose exec app composer install`
- **Database not found**: The migration command will prompt to create it automatically
- **Permission issues**: Ensure storage and bootstrap/cache directories are writable
- **Swagger not working**: Run `docker-compose exec app php artisan vendor:publish --provider "L5Swagger\L5SwaggerServiceProvider"` then `docker-compose exec app php artisan l5-swagger:generate`

## Environment Configuration

The template comes with sensible defaults for SQLite:

```env
DB_CONNECTION=sqlite
DB_DATABASE=/var/www/html/database/database.sqlite
```

## API Endpoints

### Authentication
- `POST /api/auth/login` - Login with email/password
- `POST /api/auth/register` - Register new user
- `POST /api/auth/logout` - Logout (requires authentication)
- `GET /api/auth/user` - Get current user (requires authentication)

### Health Checks
- `GET /health` - Basic health check
- `GET /health/detailed` - Detailed health check with database status

## API Documentation

The template includes automatic Swagger/OpenAPI documentation generation:

- **Interactive Documentation**: http://localhost:8000/api/documentation
- **JSON Specification**: http://localhost:8000/docs/api-docs.json
- **YAML Specification**: http://localhost:8000/docs/api-docs.yaml

### Features
- ✅ **Automatic Generation** - Documentation is generated from code annotations
- ✅ **Interactive Testing** - Test API endpoints directly from the documentation
- ✅ **Authentication Support** - Bearer token authentication for protected endpoints
- ✅ **Request/Response Examples** - Detailed examples for all endpoints
- ✅ **Validation Rules** - Automatic documentation of validation requirements

### Adding Documentation to New Endpoints

Simply add OpenAPI annotations to your controller methods:

```php
/**
 * @OA\Get(
 *     path="/api/example",
 *     summary="Example endpoint",
 *     tags={"Examples"},
 *     @OA\Response(response=200, description="Success")
 * )
 */
public function example()
{
    return response()->json(['message' => 'Success']);
}
```

## Development

### Running Tests
```bash
docker-compose exec app php artisan test
```

### Database Migrations
```bash
docker-compose exec app php artisan migrate
docker-compose exec app php artisan migrate:rollback
```

### Frontend Development
```bash
# Development mode with hot reload
npm run dev

# Build for production
npm run build
```

### Regenerating API Documentation
```bash
# Regenerate documentation after code changes
docker-compose exec app php artisan l5-swagger:generate
```

### Artisan Commands
```bash
docker-compose exec app php artisan list
```

## Production Deployment

1. Update environment variables for production
2. Set `APP_ENV=production` and `APP_DEBUG=false`
3. Run `php artisan config:cache` and `php artisan route:cache`
4. Ensure proper file permissions on storage and bootstrap/cache directories
5. Build frontend assets: `npm run build`
6. Generate API documentation: `php artisan l5-swagger:generate`

## Logging

The template includes optimized logging configuration:
- Structured JSON logging for better parsing
- Separate log files for different environments
- Error tracking and monitoring ready

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests for new functionality
5. Submit a pull request

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).