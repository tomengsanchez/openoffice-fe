# Get Role Details

## Endpoint
```
GET /settings/roles/:id
```

## Description
Retrieves detailed information about a specific role, including its assigned permissions.

## Required Permissions
- `roles:show`

## URL Parameters

| Parameter | Type   | Required | Description          |
|-----------|--------|----------|----------------------|
| id        | number | Yes      | The ID of the role   |

## Success Response (200 OK)
```json
{
  "status": "success",
  "data": {
    "id": 3,
    "role_name": "content_editor",
    "role_description": "Can create and edit content",
    "created_at": "2025-07-01T12:00:00.000000Z",
    "updated_at": "2025-07-01T12:00:00.000000Z",
    "permissions": [
      {
        "id": 1,
        "permission_name": "content:create",
        "permission_description": "Can create content",
        "created_at": "2025-01-01T00:00:00.000000Z",
        "updated_at": "2025-01-01T00:00:00.000000Z"
      },
      {
        "id": 3,
        "permission_name": "content:edit",
        "permission_description": "Can edit content",
        "created_at": "2025-01-01T00:00:00.000000Z",
        "updated_at": "2025-01-01T00:00:00.000000Z"
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

### 403 Forbidden
```json
{
  "status": "error",
  "message": "You do not have permission to view this role"
}
```

## Example Usage (JavaScript)

```javascript
async function getRole(roleId) {
  try {
    const token = localStorage.getItem('jwt_token');
    const response = await fetch(
      `http://api.openoffice.local/settings/roles/${roleId}`,
      {
        headers: {
          'Authorization': `Bearer ${token}`,
          'Accept': 'application/json'
        }
      }
    );

    const data = await response.json();
    
    if (!response.ok) {
      throw new Error(data.message || 'Failed to fetch role');
    }

    return data.data;
  } catch (error) {
    console.error('Error fetching role:', error);
    throw error;
  }
}

// Example usage:
// const role = await getRole(3);
// console.log(role.role_name, 'permissions:', role.permissions);
```

## Notes
- The response includes the role details along with an array of associated permissions
- If the role has no permissions, the `permissions` array will be empty
- The `created_at` and `updated_at` fields are in ISO 8601 format
- The endpoint is case-sensitive for the role ID
- The user must have the `roles:show` permission to access this endpoint
- This endpoint is useful for populating role edit forms with current data
