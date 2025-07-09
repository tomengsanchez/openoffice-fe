# Reset Password

## Endpoint
```
POST /reset-password
```

## Description
Resets the user's password using a valid reset token.

## Request Headers
```http
Content-Type: application/json
```

## Request Body
```json
{
  "token": "reset_token_from_email",
  "email": "user@example.com",
  "password": "newSecurePassword123",
  "password_confirmation": "newSecurePassword123"
}
```

## Success Response (200 OK)
```json
{
  "status": "success",
  "message": "Your password has been reset!"
}
```

## Error Responses

### 400 Bad Request (Invalid token)
```json
{
  "status": "error",
  "message": "This password reset token is invalid."
}
```

### 400 Bad Request (Expired token)
```json
{
  "status": "error",
  "message": "This password reset token has expired."
}
```

## Example Usage (JavaScript)

```javascript
async function resetPassword(token, email, newPassword, confirmPassword) {
  try {
    const response = await fetch('http://api.openoffice.local/reset-password', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        token,
        email,
        password: newPassword,
        password_confirmation: confirmPassword
      }),
    });

    const data = await response.json();
    
    if (!response.ok) {
      throw new Error(data.message || 'Password reset failed');
    }

    return data;
  } catch (error) {
    console.error('Password reset failed:', error);
    throw error;
  }
}
```

## Notes
- The token is typically passed as a URL parameter when the user clicks the reset link in their email
- Passwords must be at least 8 characters long
- The token is single-use and will be invalidated after successful password reset
- The user will be automatically logged out of all devices after password reset
