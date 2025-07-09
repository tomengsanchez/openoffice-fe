# User Management Guide

This guide explains how to work with the user management system in the OpenOffice application.

## User Object Structure

A user object has the following structure:

```typescript
interface User {
  id: number;
  username: string;
  email: string;
  firstname: string;  // New field
  lastname: string;   // New field
  role_id: number;
  role_name: string;
  created_at: string;
  updated_at: string;
}
```

## API Endpoints

### Get All Users

```http
GET /api/settings/users
```

**Response:**
```json
{
  "status": "success",
  "data": [
    {
      "id": 1,
      "username": "admin",
      "email": "admin@example.com",
      "firstname": "System",
      "lastname": "Administrator",
      "role_id": 1,
      "role_name": "admin",
      "created_at": "2023-01-01 00:00:00",
      "updated_at": "2023-01-01 00:00:00"
    }
  ],
  "pagination": {
    "current_page": 1,
    "per_page": 15,
    "total": 1,
    "total_pages": 1
  }
}
```

### Get Single User

```http
GET /api/settings/users/show.php?id=1
```

**Response:**
```json
{
  "status": "success",
  "data": {
    "id": 1,
    "username": "admin",
    "email": "admin@example.com",
    "firstname": "System",
    "lastname": "Administrator",
    "role_id": 1,
    "role_name": "admin",
    "created_at": "2023-01-01 00:00:00",
    "updated_at": "2023-01-01 00:00:00"
  }
}
```

### Create User

```http
POST /api/settings/users/store.php
```

**Request Body:**
```json
{
  "username": "newuser",
  "email": "newuser@example.com",
  "password": "securepassword123",
  "firstname": "New",
  "lastname": "User",
  "role_id": 2
}
```

**Response:**
```json
{
  "status": "success",
  "message": "User created successfully.",
  "data": {
    "id": 2,
    "username": "newuser",
    "email": "newuser@example.com",
    "firstname": "New",
    "lastname": "User",
    "role_id": 2,
    "role_name": "manager",
    "created_at": "2023-01-01 00:00:00",
    "updated_at": "2023-01-01 00:00:00"
  }
}
```

### Update User

```http
PUT /api/settings/users/update.php?id=2
```

**Request Body:**
```json
{
  "firstname": "Updated",
  "lastname": "Name",
  "email": "updated@example.com"
}
```

**Response:**
```json
{
  "status": "success",
  "message": "User updated successfully.",
  "data": {
    "id": 2,
    "username": "newuser",
    "email": "updated@example.com",
    "firstname": "Updated",
    "lastname": "Name",
    "role_id": 2,
    "role_name": "manager",
    "created_at": "2023-01-01 00:00:00",
    "updated_at": "2023-01-02 12:00:00"
  }
}
```

### Delete User

```http
DELETE /api/settings/users/destroy.php?id=2
```

**Response:**
```json
{
  "status": "success",
  "message": "User deleted successfully."
}
```

## Required Permissions

- `users:index` - View user list
- `users:show` - View user details
- `users:create` - Create new users
- `users:update` - Update existing users
- `users:delete` - Delete users

## Form Validation Rules

- **Username**: Required, unique, alphanumeric with underscores
- **Email**: Required, valid email format, unique
- **Password**: Required when creating, min 8 characters
- **First Name**: Required, max 100 characters
- **Last Name**: Required, max 100 characters
- **Role**: Required, must be a valid role ID

## Error Handling

All endpoints return appropriate HTTP status codes and error messages in the following format:

```json
{
  "status": "error",
  "message": "Error description"
}
```

Common error status codes:
- 400: Bad Request (validation errors)
- 401: Unauthorized (authentication required)
- 403: Forbidden (insufficient permissions)
- 404: Not Found (resource doesn't exist)
- 409: Conflict (duplicate username/email)
- 500: Internal Server Error (server-side error)
