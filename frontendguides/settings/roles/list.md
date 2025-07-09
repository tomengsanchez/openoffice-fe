# List All Roles

## Endpoint
```
GET /settings/roles
```

## Description
Retrieves a paginated list of all roles in the system.

## Required Permissions
- `roles:index`

## Query Parameters

| Parameter | Type    | Required | Description                          |
|-----------|---------|----------|--------------------------------------|
| page      | integer | No       | Page number (default: 1)             |
| per_page  | integer | No       | Items per page (default: 15, max: 100)|
| search    | string  | No       | Search term to filter roles by name  |
| sort_by   | string  | No       | Field to sort by (default: 'id')     |
| sort_dir  | string  | No       | Sort direction: 'asc' or 'desc'      |

## Success Response (200 OK)
```json
{
  "status": "success",
  "data": [
    {
      "id": 1,
      "role_name": "admin",
      "role_description": "Administrator with full access",
      "created_at": "2025-01-01T00:00:00.000000Z",
      "updated_at": "2025-01-01T00:00:00.000000Z"
    },
    {
      "id": 2,
      "role_name": "manager",
      "role_description": "Department manager",
      "created_at": "2025-01-01T00:00:00.000000Z",
      "updated_at": "2025-01-01T00:00:00.000000Z"
    }
  ],
  "meta": {
    "current_page": 1,
    "from": 1,
    "last_page": 1,
    "per_page": 15,
    "to": 2,
    "total": 2
  },
  "links": {
    "first": "http://api.openoffice.local/settings/roles?page=1",
    "last": "http://api.openoffice.local/settings/roles?page=1",
    "prev": null,
    "next": null
  }
}
```

## Example Usage (JavaScript)

```javascript
async function fetchRoles(page = 1, search = '', sortBy = 'id', sortDir = 'asc') {
  try {
    const token = localStorage.getItem('jwt_token');
    const queryParams = new URLSearchParams({
      page,
      search,
      sort_by: sortBy,
      sort_dir: sortDir
    });

    const response = await fetch(
      `http://api.openoffice.local/settings/roles?${queryParams.toString()}`,
      {
        headers: {
          'Authorization': `Bearer ${token}`,
          'Accept': 'application/json'
        }
      }
    );

    if (!response.ok) {
      const error = await response.json();
      throw new Error(error.message || 'Failed to fetch roles');
    }

    return await response.json();
  } catch (error) {
    console.error('Error fetching roles:', error);
    throw error;
  }
}

// Example usage:
// const { data, meta, links } = await fetchRoles(1, 'admin');
```

## Notes
- The response includes pagination metadata and links
- The `meta` object contains pagination details
- The `links` object contains URLs for pagination navigation
- Results can be filtered using the `search` parameter (searches in role_name and role_description)
- Sorting can be applied to any role field using `sort_by` and `sort_dir` parameters
