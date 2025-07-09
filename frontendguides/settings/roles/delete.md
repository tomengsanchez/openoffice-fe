# Delete a Role

## Endpoint
```
DELETE /settings/roles/:id
```

## Description
Deletes a role from the system. If users are assigned to this role, you must specify a role to transfer them to.

## Required Permissions
- `roles:destroy`

## URL Parameters

| Parameter | Type   | Required | Description          |
|-----------|--------|----------|----------------------|
| id        | number | Yes      | The ID of the role to delete |

## Query Parameters

| Parameter | Type   | Required | Description          |
|-----------|--------|----------|----------------------|
| transfer_to | number | No | ID of the role to transfer users to (required if role has users) |

## Success Response (200 OK)
```json
{
  "status": "success",
  "message": "Role deleted successfully"
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

### 400 Bad Request (Role has users)
```json
{
  "status": "error",
  "message": "Cannot delete role with assigned users. Please specify a role to transfer users to.",
  "data": {
    "users_count": 5
  }
}
```

### 400 Bad Request (Invalid transfer role)
```json
{
  "status": "error",
  "message": "The selected transfer role is invalid"
}
```

### 403 Forbidden
```json
{
  "status": "error",
  "message": "You do not have permission to delete roles"
}
```

## Example Usage (JavaScript)

```javascript
async function deleteRole(roleId, transferToRoleId = null) {
  try {
    const token = localStorage.getItem('jwt_token');
    let url = `http://api.openoffice.local/settings/roles/${roleId}`;
    
    // Add transfer_to parameter if provided
    if (transferToRoleId !== null) {
      const params = new URLSearchParams({ transfer_to: transferToRoleId });
      url += `?${params.toString()}`;
    }

    const response = await fetch(url, {
      method: 'DELETE',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
      }
    });

    const data = await response.json();
    
    if (!response.ok) {
      throw new Error(data.message || 'Failed to delete role');
    }

    return data;
  } catch (error) {
    console.error('Error deleting role:', error);
    throw error;
  }
}

// Example usage:
// Try to delete role 3, transfer users to role 2 if needed
// try {
//   await deleteRole(3, 2);
//   console.log('Role deleted successfully');
// } catch (error) {
//   if (error.message.includes('transfer users')) {
//     // Show UI to select a role to transfer users to
//     const transferTo = prompt('Enter role ID to transfer users to:');
//     if (transferTo) {
//       await deleteRole(3, transferTo);
//     }
//   } else {
//     console.error('Error:', error.message);
//   }
// }
```

## Notes
- Only roles with no assigned users can be deleted without specifying a transfer role
- The transfer role must be a different role than the one being deleted
- System roles (like 'admin') cannot be deleted
- The operation is irreversible
- The user must have the `roles:destroy` permission to access this endpoint
- Consider warning users about the implications of deleting a role before proceeding
- It's recommended to show a confirmation dialog before deletion
