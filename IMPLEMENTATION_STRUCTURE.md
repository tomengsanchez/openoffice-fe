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

### Authentication Flow
```mermaid
sequenceDiagram
    participant Client
    participant Auth
    participant Database
    
    Client->>Auth: POST /login {username, password}
    Auth->>Database: Verify credentials
    Database-->>Auth: User data
    Auth-->>Client: JWT Token + User Data
    
    loop Subsequent Requests
        Client->>+Server: Request with JWT
        Server->>Auth: Verify JWT
        alt Valid Token
            Server-->>-Client: Requested Data
        else Invalid/Expired Token
            Server-->>-Client: 401 Unauthorized
        end
    end
```

### Permission System
```php
// core/Auth.php
public static function check($permission_name) {
    self::init();
    $public_routes = ['auth:login', 'auth:register', 'auth:forgotPassword', 'auth:resetPassword', 'index'];

    // Allow public routes without a token
    if (in_array($permission_name, $public_routes)) {
        return;
    }

    $headers = getallheaders();
    $authHeader = $headers['Authorization'] ?? '';
    
    if (empty($authHeader) || !preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
        http_response_code(401);
        echo json_encode(['error' => 'Authorization token not found.']);
        exit;
    }

    $token = $matches[1];
    $decoded = validate_jwt($token);

    if (!$decoded) {
        http_response_code(401);
        echo json_encode(['error' => 'Invalid or expired token.']);
        exit;
    }

    // Store user payload for the request
    self::$user_payload = $decoded->data;
    
    // Check if user has required permission
    if (!self::userHasPermission($permission_name)) {
        http_response_code(403);
        echo json_encode(['error' => 'Insufficient permissions.']);
        exit;
    }
}
```

## Database Schema

### Entity Relationship Diagram
```mermaid
erDiagram
    USERS ||--o{ SERVICE_REQUESTS : creates
    USERS ||--o{ COMMENTS : writes
    ROLES ||--o{ USERS : has
    ROLES }|--|{ PERMISSIONS : has
    SERVICE_REQUESTS ||--o{ COMMENTS : has
    SERVICE_REQUESTS }|--|| USERS : assigned_to
    SERVICE_REQUESTS }|--|| CATEGORIES : belongs_to
    
    USERS {
        int id PK
        string username
        string email
        string password_hash
        string firstname
        string lastname
        int role_id FK
        datetime created_at
        datetime updated_at
    }
    
    ROLES {
        int id PK
        string name
        string description
    }
    
    PERMISSIONS {
        int id PK
        string name
        string description
    }
    
    SERVICE_REQUESTS {
        int id PK
        string title
        text description
        enum status
        enum priority
        int created_by FK
        int? assigned_to FK
        int category_id FK
        datetime created_at
        datetime updated_at
    }
    
    COMMENTS {
        int id PK
        text content
        int user_id FK
        int request_id FK
        boolean is_internal
        datetime created_at
    }
    
    CATEGORIES {
        int id PK
        string name
        string description
    }
```

### Indexing Strategy
- Primary keys: All tables have auto-incrementing integer PKs
- Foreign keys: Indexed for join performance
- Searchable fields: Username, email, request titles
- Composite indexes for common query patterns

### Data Types
- Strings: VARCHAR(255) for names, TEXT for descriptions
- Enums: Used for status and priority fields
- Timestamps: DATETIME for all created/updated fields
- Booleans: TINYINT(1) for flags like is_active, is_internal

### Database Query Example
```php
// Example from api/settings/users/index.php
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 15;
$sort_by = isset($_GET['sort_by']) ? $_GET['sort_by'] : 'u.id';
$sort_direction = isset($_GET['sort_direction']) ? $_GET['sort_direction'] : 'asc';
$search_term = isset($_GET['search']) ? $_GET['search'] : null;

// Build the base query
$base_query = "SELECT u.id, u.username, u.email, u.firstname, u.lastname, r.role_name 
               FROM users u 
               JOIN roles r ON u.role_id = r.id";

// Apply search if provided
$where_clauses = [];
$params = [];

if ($search_term) {
    $where_clauses[] = "(u.username LIKE ? OR u.email LIKE ? OR u.firstname LIKE ? OR u.lastname LIKE ?)";
    $search_param = "%$search_term%";
    $params = array_merge($params, [$search_param, $search_param, $search_param, $search_param]);
}

// Add WHERE clause if needed
if (!empty($where_clauses)) {
    $base_query .= " WHERE " . implode(" AND ", $where_clauses);
}

// Add sorting
$allowed_sort_columns = ['u.id', 'u.username', 'u.email', 'u.firstname', 'u.lastname', 'r.role_name'];
$sort_by = in_array($sort_by, $allowed_sort_columns) ? $sort_by : 'u.id';
$sort_direction = strtoupper($sort_direction) === 'DESC' ? 'DESC' : 'ASC';
$base_query .= " ORDER BY $sort_by $sort_direction";

// Get paginated results
$query = "$base_query LIMIT ? OFFSET ?";
$offset = ($page - 1) * $per_page;
$params[] = $per_page;
$params[] = $offset;

// Execute query using the global $smp database connection
global $smp;
$stmt = $smp->prepare($query);
$types = str_repeat('s', count($params));
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();
$users = $result->fetch_all(MYSQLI_ASSOC);
```

## API Endpoints

### Authentication
#### `POST /login`
Authenticate a user and retrieve a JWT token.

**Request:**
```http
POST /login HTTP/1.1
Content-Type: application/json

{
    "username": "admin",
    "password": "changeme"
}
```

**Response:**
```http
HTTP/1.1 200 OK
{
    "status": "success",
    "message": "Login successful.",
    "data": {
        "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9..."
    }
}
```

**Error Response (Invalid Credentials):**
```http
HTTP/1.1 401 Unauthorized
{
    "status": "error",
    "message": "Invalid credentials.",
    "data": null
}
```

#### `POST /logout`
Invalidate the current user's session.

### User Management
#### `GET /settings/users`
List all users with pagination and filtering.

**Query Parameters:**
- `page` - Page number (default: 1)
- `per_page` - Items per page (default: 15)
- `sort_by` - Field to sort by (u.id, u.username, u.email, r.role_name)
- `sort_direction` - Sort direction (asc, desc)
- `search` - Search term to filter users

**Response:**
```http
HTTP/1.1 200 OK
{
    "status": "success",
    "message": "Users retrieved successfully.",
    "data": [
        {
            "id": 1,
            "username": "admin",
            "email": "admin@example.com",
            "firstname": "System",
            "lastname": "Administrator",
            "role_name": "Admin"
        }
    ],
    "pagination": {
        "current_page": 1,
        "per_page": 15,
        "total": 1,
        "last_page": 1
    }
}
```

#### `GET /settings/users/{id}`
Get details of a specific user.

#### `POST /settings/users`
Create a new user.

#### `PUT /settings/users/{id}`
Update an existing user.

#### `DELETE /settings/users/{id}`
Delete a user.

### Role Management
#### `GET /settings/roles`
List all roles with permissions.

#### `GET /settings/roles/{id}`
Get details of a specific role.

### Permission Management
#### `GET /settings/permissions`
List all available permissions.
```http
POST /auth/login HTTP/1.1
Content-Type: application/json

{
    "username": "johndoe",
    "password": "securepassword123"
}

HTTP/1.1 200 OK
{
    "status": "success",
    "data": {
        "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
        "user": {
            "id": 1,
            "username": "johndoe",
            "email": "john@example.com",
            "firstname": "John",
            "lastname": "Doe",
            "role": "admin"
        }
    }
}
```

### User Management
#### `GET /settings/users`
**Query Parameters:**
- `page` - Page number (default: 1)
- `per_page` - Items per page (default: 15)
- `search` - Search in username, email, or name
- `role` - Filter by role ID
- `sort_by` - Field to sort by (id, username, email, created_at)
- `sort_dir` - Sort direction (asc, desc)

**Response:**
```json
{
    "status": "success",
    "data": [
        {
            "id": 1,
            "username": "johndoe",
            "email": "john@example.com",
            "firstname": "John",
            "lastname": "Doe",
            "role": {
                "id": 1,
                "name": "Admin"
            },
            "created_at": "2025-07-10T10:30:00Z",
            "updated_at": "2025-07-10T10:30:00Z"
        }
    ],
    "meta": {
        "current_page": 1,
        "from": 1,
        "last_page": 5,
        "per_page": 15,
        "to": 15,
        "total": 75
    }
}
```

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
1. Manual testing with test users
2. API testing using Postman or similar tools
3. Verify all endpoints and error cases

### Deployment
1. Merge to `main` branch
2. Run database migrations
3. Update server code
4. Clear cache if needed

## Project File Structure

### 1. Core Files
- `index.php` - Main entry point
- `.htaccess` - URL rewriting and security rules
- `config.php` - Main configuration file

### 2. Core Components
- `core/`
  - `Router.php` - Handles routing and request dispatching
  - `Database.php` - Database connection and query builder
  - `Auth.php` - Authentication and authorization logic
  - `jwt_handler.php` - JWT token creation and validation
  - `helpers/` - Utility functions and helpers
    - `pagination.php` - Pagination logic
    - `response.php` - Standardized response formatting

### 3. API Endpoints
- `api/`
  - `auth/`
    - `login.php` - User authentication
    - `logout.php` - Session invalidation
  - `settings/`
    - `users/` - User management
      - `index.php` - List users
      - `store.php` - Create user
      - `show.php` - Get user details
      - `update.php` - Update user
      - `destroy.php` - Delete user
    - `roles/` - Role management
      - `index.php` - List roles
      - `store.php` - Create role
      - `show.php` - Get role details
      - `update.php` - Update role
      - `destroy.php` - Delete role
    - `permissions/` - Permission management
      - `index.php` - List permissions
      - `store.php` - Create permission
      - `show.php` - Get permission details
      - `update.php` - Update permission
      - `destroy.php` - Delete permission

### 4. Database
- `rebuild_database.sql` - Complete database schema and initial data
- `seeders/` - Database seeders for test data
  - `UserSeeder.php` - Sample user data
  - `RoleSeeder.php` - Default roles and permissions

### 5. Documentation
- `IMPLEMENTATION_STRUCTURE.md` - Technical documentation (this file)
- `FRONTEND_DEVELOPER_GUIDE.md` - API usage guide for frontend developers

### 6. Configuration
- `.env` - Environment variables (database, JWT secrets, etc.)
- `composer.json` - PHP dependencies and autoloading

### 7. Public Assets
- `public/` - Publicly accessible files (CSS, JS, images)
  - `index.php` - Frontend entry point (if applicable)
  - `.htaccess` - Public access rules

### 8. Logs
- `logs/` - Application logs
  - `error.log` - Error logs
  - `access.log` - Access logs

### 9. Vendor (Composer)
- `vendor/` - Third-party dependencies (managed by Composer)

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
