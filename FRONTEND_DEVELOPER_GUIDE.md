# OpenOffice API - Frontend Developer Guide

## Table of Contents
1. [Introduction](#introduction)
2. [Authentication](#authentication)
3. [API Base URL](#api-base-url)
4. [Making Requests](#making-requests)
5. [Available Endpoints](#available-endpoints)
   - [Authentication](#authentication-endpoints)
   - [User Management](#user-management)
   - [Role Management](#role-management)
   - [Permission Management](#permission-management)
   - [Service Requests](#service-requests)
6. [Pagination, Sorting & Filtering](#pagination-sorting--filtering)
7. [Error Handling](#error-handling)
8. [Development Setup](#development-setup)
9. [Testing Users](#testing-users)

## Introduction

Welcome to the OpenOffice API! This guide will help frontend developers integrate with our RESTful API. The API follows standard REST conventions and uses JWT for authentication.

## Authentication

### Login
```http
POST /login
Content-Type: application/json

{
    "username": "admin",
    "password": "password123"
}
```

### Response
```json
{
    "status": "success",
    "message": "Login successful",
    "data": {
        "token": "your.jwt.token.here",
        "user": {
            "id": 1,
            "username": "admin",
            "email": "admin@example.com",
            "role_id": 1
        }
    },
    "available_links": [
        // Array of routes the user has access to
    ]
}
```

### Using the Token
Include the JWT token in the `Authorization` header for all authenticated requests:
```
Authorization: Bearer your.jwt.token.here
```

## API Base URL
```
http://your-api-domain.com/api
```

## Making Requests
All API responses follow this format:
```typescript
{
    status: 'success' | 'error',
    message: string,
    data: any,
    pagination?: {
        total: number,
        per_page: number,
        current_page: number,
        last_page: number,
        from: number,
        to: number
    },
    available_links?: Array<{
        method: string,
        url: string,
        label: string,
        permission: string
    }>,
    logged_in_user?: User
}
```

## Available Endpoints

### Authentication Endpoints

#### `POST /login`
Authenticate and receive JWT token.
- `GET /settings/users/{id}` - Get user details
- `PUT /settings/users/{id}` - Update user
- `DELETE /settings/users/{id}` - Delete user

### Role Management (Admin)
- `GET /settings/roles` - List roles (paginated)
- `POST /settings/roles` - Create new role
- `GET /settings/roles/{id}` - Get role details
- `PUT /settings/roles/{id}` - Update role
- `DELETE /settings/roles/{id}` - Delete role

### Permission Management (Admin)
- `GET /settings/permissions` - List all available permissions (paginated)
- `GET /settings/permissions/{id}` - Get permission details
- `POST /settings/permissions` - Create new permission
- `PUT /settings/permissions/{id}` - Update permission
- `DELETE /settings/permissions/{id}` - Delete permission

#### `GET /settings/permissions` - List Permissions

**Request:**
```http
GET /settings/permissions
Authorization: Bearer your.jwt.token.here
```

**Query Parameters:**
- `page` - Page number (default: 1)
- `per_page` - Items per page (default: 15)
- `sort` - Sort field (e.g., `name`, `-created_at` for descending)
- `search` - Search term to filter permissions by name or description

**Response (200 OK):**
```json
{
    "status": "success",
    "data": [
        {
            "id": 1,
            "name": "users:view",
            "description": "View user accounts",
            "created_at": "2023-01-01T00:00:00.000000Z",
            "updated_at": "2023-01-01T00:00:00.000000Z"
        }
    ],
    "pagination": {
        "total": 15,
        "per_page": 15,
        "current_page": 1,
        "last_page": 2
    }
}
```

#### `GET /settings/permissions/{id}` - Get Permission Details

**Request:**
```http
GET /settings/permissions/1
Authorization: Bearer your.jwt.token.here
```

**Response (200 OK):**
```json
{
    "status": "success",
    "data": {
        "id": 1,
        "name": "users:view",
        "description": "View user accounts",
        "created_at": "2023-01-01T00:00:00.000000Z",
        "updated_at": "2023-01-01T00:00:00.000000Z"
    }
}
```

#### `POST /settings/permissions` - Create Permission

**Request:**
```http
POST /settings/permissions
Authorization: Bearer your.jwt.token.here
Content-Type: application/json

{
    "name": "reports:generate",
    "description": "Generate system reports"
}
```

**Response (201 Created):**
```json
{
    "status": "success",
    "message": "Permission created successfully",
    "data": {
        "id": 16,
        "name": "reports:generate",
        "description": "Generate system reports",
        "created_at": "2023-01-01T00:00:00.000000Z",
        "updated_at": "2023-01-01T00:00:00.000000Z"
    }
}
```

#### `PUT /settings/permissions/{id}` - Update Permission

**Request:**
```http
PUT /settings/permissions/16
Authorization: Bearer your.jwt.token.here
Content-Type: application/json

{
    "description": "Generate and view system reports"
}
```

**Response (200 OK):**
```json
{
    "status": "success",
    "message": "Permission updated successfully",
    "data": {
        "id": 16,
        "name": "reports:generate",
        "description": "Generate and view system reports",
        "created_at": "2023-01-01T00:00:00.000000Z",
        "updated_at": "2023-01-02T00:00:00.000000Z"
    }
}
```

#### `DELETE /settings/permissions/{id}` - Delete Permission

**Request:**
```http
DELETE /settings/permissions/16
Authorization: Bearer your.jwt.token.here
```

**Response (200 OK):**
```json
{
    "status": "success",
    "message": "Permission deleted successfully"
}
```

### Dynamic Routes

#### `GET /routes` - List Available Routes

This endpoint returns all routes that the currently authenticated user has permission to access. This is particularly useful for dynamically generating navigation menus and controlling UI elements based on user permissions.

**Request:**
```http
GET /routes
Authorization: Bearer your.jwt.token.here
```

**Response (200 OK):**
```json
{
    "status": "success",
    "data": [
        {
            "method": "GET",
            "url": "/dashboard",
            "handler": "dashboard/index",
            "permission": "view_dashboard",
            "label": "Dashboard",
            "description": "Main dashboard overview",
            "icon": "speedometer2"
        },
        {
            "method": "GET",
            "url": "/settings/users",
            "handler": "settings/users/index",
            "permission": "users:view",
            "label": "User Management",
            "description": "Manage system users",
            "icon": "people"
        }
    ]
}
```

**Response (401 Unauthorized):**
```json
{
    "status": "error",
    "message": "Unauthenticated."
}
```

**Response (403 Forbidden):**
```json
{
    "status": "error",
    "message": "You don't have permission to access this resource."
}
```

**Frontend Implementation Example:**

1. **Fetching Routes on Login**
```javascript
async function fetchUserRoutes() {
    try {
        const response = await fetch('/proxy_api.php?endpoint=/routes', {
            headers: {
                'Authorization': `Bearer ${getToken()}`,
                'Content-Type': 'application/json'
            }
        });
        
        if (!response.ok) {
            throw new Error('Failed to fetch routes');
        }
        
        const data = await response.json();
        if (data.status === 'success') {
            // Store routes in your state management
            store.dispatch('setRoutes', data.data);
            return data.data;
        }
    } catch (error) {
        console.error('Error fetching routes:', error);
        // Fallback to default routes if needed
        return getDefaultRoutes();
    }
}
```

2. **Generating Navigation**
```javascript
function generateNavigation(routes) {
    return routes
        .filter(route => route.method === 'GET')
        .map(route => ({
            path: route.url,
            name: route.label,
            icon: route.icon,
            permission: route.permission
        }));
}
```

3. **Checking Route Permissions**
```javascript
function canAccessRoute(requiredPermission, userPermissions) {
    if (!requiredPermission) return true;
    return userPermissions.includes(requiredPermission);
}
```

**Best Practices:**
- Cache the routes after first fetch to reduce API calls
- Implement route-based code splitting for better performance
- Handle route changes and permission updates appropriately
- Always validate routes on the server-side, even if you check permissions in the frontend
- Use the `available_links` from the login response for initial navigation before fetching full routes
- Implement proper error handling for when the routes endpoint is unavailable
- Consider implementing route-based access control in your frontend router

**Route Object Structure:**

| Field | Type | Description |
|-------|------|-------------|
| method | string | HTTP method (GET, POST, PUT, DELETE) |
| url | string | API endpoint URL |
| handler | string | Internal handler path |
| permission | string | Required permission to access this route |
| label | string | Display name for the route |
| description | string | Optional description |
| icon | string | Icon class/name for UI |
| group | string | Optional group/category for organization |
| order | number | Sort order within group |
| is_menu_item | boolean | Whether to show in main navigation |

## Pagination, Sorting & Filtering

### Pagination
All list endpoints support pagination:
```
GET /settings/users?page=1&per_page=15
```

### Sorting
Sort by any field with `sort_by` and `sort_direction`:
```
GET /settings/users?sort_by=username&sort_direction=asc
```

### Filtering
Search across searchable fields:
```
GET /settings/users?search=john
```

## Error Handling

The API uses standard HTTP status codes and provides detailed error messages in the response body.

### Common Status Codes
- `200 OK` - Request successful
- `201 Created` - Resource created successfully
- `204 No Content` - Resource deleted successfully
- `400 Bad Request` - Invalid request parameters
- `401 Unauthorized` - Authentication required
- `403 Forbidden` - Insufficient permissions
- `404 Not Found` - Resource not found
- `422 Unprocessable Entity` - Validation errors
- `500 Internal Server Error` - Server error

### Error Response Format
```json
{
    "status": "error",
    "message": "Error description",
    "errors": {
        "field_name": ["Error message"]
    }
}
```

### Handling JWT Tokens
1. Include the JWT in the `Authorization` header for authenticated requests:
   ```
   Authorization: Bearer your.jwt.token.here
   ```
2. Handle token expiration (401 status code) by:
   - Prompting the user to log in again
   - Storing refresh tokens if implemented
   - Implementing token refresh logic

## Rate Limiting
- 60 requests per minute per IP address
- 1000 requests per day per user
- Headers are returned with rate limit information:
  - `X-RateLimit-Limit`: Maximum requests allowed
  - `X-RateLimit-Remaining`: Remaining requests
  - `X-RateLimit-Reset`: Timestamp when the limit resets

### Common Error Responses

#### 401 Unauthorized
```json
{
    "status": "error",
    "message": "Unauthenticated."
}
```

#### 403 Forbidden
```json
{
    "status": "error",
    "message": "You don't have permission to access this resource."
}
```

#### 404 Not Found
```json
{
    "status": "error",
    "message": "Resource not found."
}
```

#### 422 Validation Error
```json
{
    "status": "error",
    "message": "The given data was invalid.",
    "errors": {
        "email": ["The email field is required."],
        "password": ["The password must be at least 8 characters."]
    }
}
```

## Development Setup

1. Clone the repository
2. Install dependencies: `composer install`
3. Copy `.env.example` to `.env` and configure your environment
4. Run the development server: `php -S localhost:8000 -t public`

## Testing Users

We've pre-seeded the database with test users. You can use these credentials:

| Username | Password   | Role      |
|----------|------------|-----------|
| admin    | password123| Admin     |
| manager1 | password123| Manager   |
| user1    | password123| Employee  |

Or use any of the 10,000 test users created by the seeder (all with password `password123`).

## Frontend Implementation Tips

1. **Store the JWT token** in localStorage or a secure HTTP-only cookie
2. **Handle token expiration** - implement automatic logout or token refresh
3. **Use available_links** to dynamically generate navigation based on user permissions
4. **Implement proper error handling** for API responses
5. **Show loading states** during API calls
6. **Validate forms** both client and server-side
7. **Implement proper error messages** from the API responses

## Rate Limiting
API is rate limited to 60 requests per minute per IP address. Include proper error handling for 429 responses.

## Support
For any questions or issues, please contact the backend development team or create an issue in the repository.
