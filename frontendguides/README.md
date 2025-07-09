# OpenOffice API Frontend Guides

This directory contains comprehensive guides for frontend developers working with the OpenOffice API. Each guide focuses on a specific API endpoint or group of related endpoints.

## Available Guides

### Authentication
- [Authentication Guide](./authentication.md) - User login, registration, and token management

### Users
- [Users API Guide](./users.md) - User management endpoints

### Roles
- [Roles API Guide](./roles.md) - Role management and assignment

### Permissions
- [Permissions API Guide](./permissions.md) - Permission management

### IT Service Requests
- [IT Service Requests Guide](./it-service-requests.md) - Managing IT service tickets

### General
- [Error Handling](./error-handling.md) - Common error responses and handling
- [Pagination](./pagination.md) - Working with paginated responses

## Base URL
All API endpoints are relative to: `http://api.openoffice.local`

## Authentication
Most endpoints require a JWT token in the Authorization header:
```
Authorization: Bearer YOUR_JWT_TOKEN
```

## Common Headers
```http
Content-Type: application/json
Accept: application/json
Authorization: Bearer YOUR_JWT_TOKEN
```

## Rate Limiting
- 60 requests per minute per IP address
- 1000 requests per day per user

## Versioning
Current API version: `v1`

## Support
For any questions or issues, please contact the development team.
