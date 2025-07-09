# Create a New Role

## Endpoint
```
POST /settings/roles
```

## Description
Creates a new role with the specified permissions.

## Required Permissions
- `roles:store`

## Request Body

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| role_name | string | Yes | Unique name for the role |
| role_description | string | No | Description of the role |
| permissions | array | No | Array of permission IDs to assign to this role |

### Example Request Body
```json
{
  "role_name": "content_editor",
  "role_description": "Can create and edit content",
  "permissions": [1, 3, 5]
}
```

## Success Response (201 Created)
```json
{
  "status": "success",
  "message": "Role created successfully",
  "data": {
    "id": 5,
    "role_name": "content_editor",
    "role_description": "Can create and edit content",
    "created_at": "2025-07-09T02:49:58.000000Z",
    "updated_at": "2025-07-09T02:49:58.000000Z",
    "permissions": [
      {
        "id": 1,
        "permission_name": "content:create",
        "permission_description": "Can create content"
      },
      {
        "id": 3,
        "permission_name": "content:edit",
        "permission_description": "Can edit content"
      },
      {
        "id": 5,
        "permission_name": "media:upload",
        "permission_description": "Can upload media files"
      }
    ]
  }
}
```

## Error Responses

### 422 Unprocessable Entity (Validation Error)
```json
{
  "status": "error",
  "message": "The given data was invalid.",
  "errors": {
    "role_name": [
      "The role name field is required.",
      "The role name has already been taken."
    ]
  }
}
```

### 403 Forbidden
```json
{
  "status": "error",
  "message": "You do not have permission to create roles"
}
```

## Example Usage (JavaScript)

```javascript
async function createRole(roleData) {
  try {
    const token = localStorage.getItem('jwt_token');
    const response = await fetch('http://api.openoffice.local/settings/roles', {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify(roleData)
    });

    const data = await response.json();
    
    if (!response.ok) {
      throw new Error(data.message || 'Failed to create role');
    }

    return data;
  } catch (error) {
    console.error('Error creating role:', error);
    throw error;
  }
}

// Example usage:
// const newRole = await createRole({
//   role_name: 'content_editor',
//   role_description: 'Can create and edit content',
//   permissions: [1, 3, 5]
// });
```

## Notes
- The `role_name` must be unique across the system
- Permission IDs that don't exist will be ignored
- The role will be created with no permissions if the `permissions` array is empty or not provided
- The response includes the created role with its assigned permissions
- The user must have the `roles:store` permission to access this endpoint
