# OpenOffice API - Implementation Structure

## Table of Contents
1. [Core Architecture](#core-architecture)
2. [Directory Structure](#directory-structure)
3. [Authentication Flow](#authentication-flow)
4. [Database Schema](#database-schema)
5. [API Endpoints](#api-endpoints)
6. [Error Handling](#error-handling)
7. [Security](#security)
8. [Development Workflow](#development-workflow)

## Core Architecture

### Technology Stack
- **Backend**: PHP 8.0+
- **Database**: MySQL 8.0+
- **Authentication**: JWT (JSON Web Tokens)
- **Web Server**: Apache/Nginx
- **API Style**: RESTful

### Key Components
1. **Authentication System**
   - JWT-based authentication
   - Role-based access control (RBAC)
   - Permission system

2. **Request Pipeline**
   - Request validation
   - Authentication middleware
   - Permission checking
   - Response formatting

3. **Database Layer**
   - Direct MySQLi usage
   - Prepared statements for security
   - Transaction support

## Directory Structure

```
op-api/
├── api/                    # API endpoints
│   ├── auth/               # Authentication endpoints
│   ├── settings/           # System settings
│   │   ├── users/          # User management
│   │   ├── roles/          # Role management
│   │   └── permissions/    # Permission management
│   └── it/                 # IT service endpoints
│       └── service-requests/ # Service request management
├── core/                   # Core application files
│   ├── Auth.php            # Authentication logic
│   ├── Database.php        # Database connection
│   └── helpers/            # Helper functions
│       └── pagination.php  # Pagination logic
├── database/
│   └── migrations/         # Database migrations
├── frontendguides/         # Frontend documentation
└── seeders/                # Database seeders
```

## Authentication Flow

### JWT Authentication
1. Client sends credentials to `/login`
2. Server validates credentials and issues JWT
3. Client includes JWT in `Authorization` header
4. Middleware validates token on each request

### Permission System
- Permissions are assigned to roles
- Users are assigned roles
- Endpoints check for required permissions

## Database Schema

### Key Tables
1. **users**
   - id, username, email, password_hash, firstname, lastname, role_id
   - Timestamps for creation/updates

2. **roles**
   - id, role_name, description

3. **permissions**
   - id, permission_name, description

4. **role_permissions**
   - role_id, permission_id

5. **service_requests**
   - id, title, description, status, priority
   - Relationships to users and categories

## API Endpoints

### Authentication
- `POST /login` - Get JWT token
- `POST /logout` - Invalidate token

### User Management
- `GET /settings/users` - List users
- `POST /settings/users` - Create user
- `GET/PUT/DELETE /settings/users/{id}` - User CRUD

### Service Requests
- `GET /it/service-requests` - List requests
- `POST /it/service-requests` - Create request
- `GET/PUT/DELETE /it/service-requests/{id}` - Request CRUD
- `POST /it/service-requests/{id}/comments` - Add comment

## Error Handling

### Response Format
```json
{
    "status": "error",
    "message": "Error description",
    "errors": {
        "field": ["Error message"]
    }
}
```

### Common Status Codes
- 200 OK - Success
- 201 Created - Resource created
- 400 Bad Request - Invalid input
- 401 Unauthorized - Not authenticated
- 403 Forbidden - Insufficient permissions
- 404 Not Found - Resource not found
- 422 Unprocessable Entity - Validation errors
- 500 Internal Server Error - Server error

## Security

### Input Validation
- All user input is validated
- Use prepared statements for database queries
- Sanitize output

### Rate Limiting
- 60 requests/minute per IP
- 1000 requests/day per user

### CORS
- Configured to allow requests from trusted domains
- Preflight request support

## Development Workflow

### Setup
1. Clone repository
2. Run `composer install`
3. Configure `.env` file
4. Import database schema
5. Run database migrations

### Testing
1. Unit tests: `phpunit`
2. API tests: Postman collection
3. Manual testing with test users

### Deployment
1. Merge to `main` branch
2. Run database migrations
3. Update server code
4. Clear cache if needed

## Best Practices

### Code Style
- Follow PSR-12 coding standards
- Use type hints where possible
- Document complex logic

### Security
- Never commit sensitive data
- Use environment variables for configuration
- Keep dependencies updated

### Performance
- Optimize database queries
- Use indexes appropriately
- Implement caching where beneficial
