# Roles Management

This directory contains documentation for managing user roles and their associated permissions.

## Available Endpoints

1. [List All Roles](./list.md) - `GET /settings/roles`
2. [Create a New Role](./create.md) - `POST /settings/roles`
3. [Get Role Details](./show.md) - `GET /settings/roles/:id`
4. [Update a Role](./update.md) - `PUT /settings/roles/:id`
5. [Delete a Role](./delete.md) - `DELETE /settings/roles/:id`

## Required Permissions

- `roles:index` - View roles list
- `roles:show` - View role details
- `roles:store` - Create new roles
- `roles:update` - Update existing roles
- `roles:destroy` - Delete roles

## Common Headers

```http
Authorization: Bearer <token>
Content-Type: application/json
Accept: application/json
```

## Data Types

### Role Object
```typescript
interface Role {
  id: number;
  role_name: string;
  role_description: string | null;
  created_at: string | null;
  updated_at: string | null;
  permissions?: Permission[];
}

interface Permission {
  id: number;
  permission_name: string;
  permission_description: string | null;
  created_at: string;
  updated_at: string;
}
```

## Error Responses

### Common Error Responses

#### 401 Unauthorized
```json
{
  "status": "error",
  "message": "Unauthenticated"
}
```

#### 403 Forbidden
```json
{
  "status": "error",
  "message": "You do not have permission to perform this action"
}
```

#### 404 Not Found
```json
{
  "status": "error",
  "message": "Role not found"
}
```

#### 422 Unprocessable Entity (Validation Error)
```json
{
  "status": "error",
  "message": "The given data was invalid.",
  "errors": {
    "field_name": ["The field name is required."]
  }
}
```
