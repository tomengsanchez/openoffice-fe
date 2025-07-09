# Forgot Password

## Endpoint
```
POST /forgot-password
```

## Description
Initiates the password reset process by sending a reset link to the user's email.

## Request Headers
```http
Content-Type: application/json
```

## Request Body
```json
{
  "email": "user@example.com"
}
```

## Success Response (200 OK)
```json
{
  "status": "success",
  "message": "Password reset link sent to your email"
}
```

## Error Responses

### 404 Not Found
```json
{
  "status": "error",
  "message": "We can't find a user with that email address."
}
```

## Example Usage (JavaScript)

```javascript
async function forgotPassword(email) {
  try {
    const response = await fetch('http://api.openoffice.local/forgot-password', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({ email }),
    });

    const data = await response.json();
    
    if (!response.ok) {
      throw new Error(data.message || 'Failed to send reset link');
    }

    return data;
  } catch (error) {
    console.error('Password reset request failed:', error);
    throw error;
  }
}
```

## Notes
- This endpoint will send an email with a password reset link
- The reset link will be valid for 60 minutes
- The email will not be sent if the email doesn't exist in the system (for security reasons)
