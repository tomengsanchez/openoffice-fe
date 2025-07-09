# User Registration

## Endpoint
```
POST /register
```

## Description
Registers a new user account.

## Request Headers
```http
Content-Type: application/json
```

## Request Body
```json
{
  "username": "newuser@example.com",
  "password": "securePassword123",
  "password_confirmation": "securePassword123",
  "name": "New User"
}
```

## Success Response (201 Created)
```json
{
  "status": "success",
  "data": {
    "id": 123,
    "username": "newuser@example.com",
    "name": "New User",
    "role": "user",
    "created_at": "2025-07-09T10:00:00Z"
  }
}
```

## Error Responses

### 422 Unprocessable Entity (Validation Error)
```json
{
  "status": "error",
  "message": "Validation failed",
  "errors": {
    "username": ["The username has already been taken."],
    "password": ["The password must be at least 8 characters."]
  }
}
```

## Example Usage (JavaScript)

```javascript
async function register(userData) {
  try {
    const response = await fetch('http://api.openoffice.local/register', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify(userData),
    });

    const data = await response.json();
    
    if (!response.ok) {
      throw new Error(data.message || 'Registration failed');
    }

    return data.data;
  } catch (error) {
    console.error('Registration error:', error);
    throw error;
  }
}
```

## Notes
- Passwords must be at least 8 characters long
- Email must be unique and properly formatted
- The API will automatically log in the user and return a token in the response
