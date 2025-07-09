# Update a Role

## Endpoint
```
PUT /settings/roles/:id
```

## Description
Updates an existing role's details and permissions.

## Required Permissions
- `roles:update`

## URL Parameters

| Parameter | Type   | Required | Description          |
|-----------|--------|----------|----------------------|
| id        | number | Yes      | The ID of the role to update |

## Request Body

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| role_name | string | No | New name for the role (must be unique) |
| role_description | string | No | New description for the role |
| permissions | array | No | Array of permission IDs to assign to this role (replaces all existing permissions) |

### Example Request Body
```json
{
  "role_name": "senior_editor",
  "role_description": "Can manage all content and users",
  "permissions": [1, 2, 3, 4, 5]
}
```

## Success Response (200 OK)
```json
{
  "status": "success",
  "message": "Role updated successfully",
  "data": {
    "id": 3,
    "role_name": "senior_editor",
    "role_description": "Can manage all content and users",
    "created_at": "2025-07-01T12:00:00.000000Z",
    "updated_at": "2025-07-09T03:00:00.000000Z",
    "permissions": [
      {
        "id": 1,
        "permission_name": "content:create",
        "permission_description": "Can create content"
      },
      {
        "id": 2,
        "permission_name": "content:delete",
        "permission_description": "Can delete content"
      },
      {
        "id": 3,
        "permission_name": "content:edit",
        "permission_description": "Can edit content"
      },
      {
        "id": 4,
        "permission_name": "users:manage",
        "permission_description": "Can manage users"
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

### 404 Not Found
```json
{
  "status": "error",
  "message": "Role not found"
}
```

### 422 Unprocessable Entity (Validation Error)
```json
{
  "status": "error",
  "message": "The given data was invalid.",
  "errors": {
    "role_name": [
      "The role name has already been taken."
    ]
  }
}
```

### 403 Forbidden
```json
{
  "status": "error",
  "message": "You do not have permission to update roles"
}
```

## Example Usage (JavaScript)

```javascript
async function updateRole(roleId, updateData) {
  try {
    const token = localStorage.getItem('jwt_token');
    const response = await fetch(
      `http://api.openoffice.local/settings/roles/${roleId}`,
      {
        method: 'PUT',
        headers: {
          'Authorization': `Bearer ${token}`,
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        },
        body: JSON.stringify(updateData)
      }
    );

    const data = await response.json();
    
    if (!response.ok) {
      throw new Error(data.message || 'Failed to update role');
    }

    return data;
  } catch (error) {
    console.error('Error updating role:', error);
    throw error;
  }
}

// Example usage:
// const result = await updateRole(3, {
//   role_name: 'senior_editor',
//   role_description: 'Can manage all content and users',
//   permissions: [1, 2, 3, 4, 5]
// });
```

## Notes
- Only include the fields you want to update in the request body
- The `permissions` array will completely replace any existing permissions for the role
- To remove all permissions from a role, send an empty array: `"permissions": []`
- The `updated_at` timestamp will be automatically updated
- The user must have the `roles:update` permission to access this endpoint
- Changes to role permissions take effect immediately for all users with that role
