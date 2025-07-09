# Logout Endpoint

## Endpoint
```
POST /logout
```

## Description
Invalidates the current user's authentication token.

## Request Headers
```http
Authorization: Bearer <token>
Content-Type: application/json
```

## Request Body
None required

## Success Response (200 OK)
```json
{
  "status": "success",
  "message": "Successfully logged out"
}
```

## Error Responses

### 401 Unauthorized
```json
{
  "status": "error",
  "message": "Unauthenticated"
}
```

## Example Usage (JavaScript)

```javascript
async function logout() {
  try {
    const token = localStorage.getItem('jwt_token');
    const response = await fetch('http://api.openoffice.local/logout', {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json',
      },
    });

    const data = await response.json();
    
    if (!response.ok) {
      throw new Error(data.message || 'Logout failed');
    }

    // Clear the token from storage
    localStorage.removeItem('jwt_token');
    return data;
  } catch (error) {
    console.error('Logout error:', error);
    throw error;
  }
}
```

## Notes
- The token will be invalidated on the server side
- Client should remove the token from storage after successful logout
- All subsequent requests with the invalidated token will be rejected
