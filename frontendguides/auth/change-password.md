# Change Password

## Endpoint
```
POST /change-password
```

## Description
Allows an authenticated user to change their password.

## Request Headers
```http
Authorization: Bearer <token>
Content-Type: application/json
```

## Request Body
```json
{
  "current_password": "currentPassword123",
  "new_password": "newSecurePassword123",
  "new_password_confirmation": "newSecurePassword123"
}
```

## Success Response (200 OK)
```json
{
  "status": "success",
  "message": "Password updated successfully"
}
```

## Error Responses

### 401 Unauthorized (Incorrect current password)
```json
{
  "status": "error",
  "message": "Current password is incorrect"
}
```

### 422 Unprocessable Entity (Validation Error)
```json
{
  "status": "error",
  "message": "The given data was invalid.",
  "errors": {
    "new_password": ["The new password must be at least 8 characters."]
  }
}
```

## Example Usage (JavaScript)

```javascript
async function changePassword(currentPassword, newPassword, confirmPassword) {
  try {
    const token = localStorage.getItem('jwt_token');
    const response = await fetch('http://api.openoffice.local/change-password', {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        current_password: currentPassword,
        new_password: newPassword,
        new_password_confirmation: confirmPassword
      }),
    });

    const data = await response.json();
    
    if (!response.ok) {
      throw new Error(data.message || 'Password change failed');
    }

    return data;
  } catch (error) {
    console.error('Password change failed:', error);
    throw error;
  }
}
```

## Notes
- The user must be authenticated to access this endpoint
- The new password must be different from the current password
- The user will be automatically logged out of all other devices after password change
- It's recommended to prompt the user to log in again after changing their password
