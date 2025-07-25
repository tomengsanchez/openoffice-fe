# OpenOffice API

A RESTful API for OpenOffice management system with role-based access control.

## Features

- **User Authentication** (JWT-based)
- **Role-Based Access Control** (RBAC)
- **Permission Management**
- **User Management**
- **IT Service Request Management**
- **Dynamic Route Handling**

## Tech Stack

- **Backend**: PHP 8.1+
- **Database**: MySQL 8.0+
- **Authentication**: JWT
- **API**: RESTful

## Getting Started

### Prerequisites

- PHP 8.1 or higher
- MySQL 8.0 or higher
- Composer
- Web server (Apache/Nginx)

### Installation

1. Clone the repository:
   ```bash
   git clone https://github.com/yourusername/op-api.git
   cd op-api
   ```

2. Install dependencies:
   ```bash
   composer install
   ```

3. Configure the database:
   - Create a new MySQL database
   - Import the database schema:
     ```bash
     mysql -u username -p database_name < rebuild_database.sql
     ```

4. Configure the application:
   - Copy `.env.example` to `.env`
   - Update the database credentials and other settings in `.env`

5. Generate application key:
   ```bash
   php artisan key:generate
   ```

6. Set up file permissions:
   ```bash
   chmod -R 755 storage
   chmod -R 755 bootstrap/cache
   ```

## API Documentation

### Base URL
```
http://your-domain.com/api
```

### Authentication
All endpoints (except login/register) require a JWT token in the Authorization header:
```
Authorization: Bearer your_jwt_token
```

### Endpoints

#### Authentication
- `POST /login` - User login
- `POST /register` - Register new user
- `POST /logout` - Logout user
- `POST /forgot-password` - Request password reset
- `POST /reset-password` - Reset password
- `POST /change-password` - Change password (authenticated)

#### Permissions
- `GET /settings/permissions` - List all permissions
- `GET /settings/permissions/:id` - Get single permission
- `POST /settings/permissions` - Create new permission
- `PUT /settings/permissions/:id` - Update permission
- `DELETE /settings/permissions/:id` - Delete permission

#### Roles
- `GET /settings/roles` - List all roles
- `POST /settings/roles` - Create new role
- `PUT /settings/roles/:id` - Update role
- `DELETE /settings/roles/:id` - Delete role

#### Users
- `GET /users` - List all users
- `GET /users/:id` - Get single user
- `POST /users` - Create new user
- `PUT /users/:id` - Update user
- `DELETE /users/:id` - Delete user

#### IT Service Requests
- `GET /it-service-requests` - List all requests
- `GET /it-service-requests/:id` - Get single request
- `POST /it-service-requests` - Create new request
- `PUT /it-service-requests/:id` - Update request
- `DELETE /it-service-requests/:id` - Delete request
- `POST /it-service-requests/:id/change-status` - Change request status

## Default Users

### Admin User
- **Username**: admin
- **Password**: changeme

### Manager User
- **Username**: manager
- **Password**: changeme

### Employee User
- **Username**: employee
- **Password**: changeme

## Development

### Database Migrations
To apply database changes:
```bash
php artisan migrate
```

### Seeding the Database
To seed the database with test data:
```bash
php artisan db:seed
```

### Testing
Run the test suite:
```bash
composer test
```

## Security

- All passwords are hashed using bcrypt
- JWT tokens expire after 24 hours by default
- CSRF protection is enabled
- Input validation on all endpoints

## Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## Support

For support, please open an issue in the GitHub repository.
