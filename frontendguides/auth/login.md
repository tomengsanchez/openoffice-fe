# Login Endpoint

## Endpoint
```
POST /login
```

## Description
Authenticates a user and returns a JWT token for subsequent requests.

## Request Headers
```http
Content-Type: application/json
```

## Request Body
```json
{
  "username": "user@example.com",
  "password": "yourpassword"
}
```

## Success Response (200 OK)
```json
{
  "status": "success",
  "data": {
    "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
    "user": {
      "id": 1,
      "username": "user@example.com",
      "role": "admin"
    }
  }
}
```

## Error Responses

### 401 Unauthorized (Invalid credentials)
```json
{
  "status": "error",
  "message": "Invalid credentials"
}
```

### 400 Bad Request (Missing fields)
```json
{
  "status": "error",
  "message": "Username and password are required"
}
```

## Example Usage (JavaScript)

```javascript
async function login(username, password) {
  try {
    const response = await fetch('http://api.openoffice.local/login', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({ username, password }),
    });

    const data = await response.json();
    
    if (!response.ok) {
      throw new Error(data.message || 'Login failed');
    }

    // Save token to localStorage or state management
    localStorage.setItem('jwt_token', data.data.token);
    return data.data.user;
  } catch (error) {
    console.error('Login error:', error);
    throw error;
  }
}
```

## Notes
- The JWT token should be included in the `Authorization` header for subsequent requests
- Token format: `Authorization: Bearer <token>`
- The token has a default expiration of 24 hours
