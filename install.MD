# OpenOffice API Installation Guide

This guide provides step-by-step instructions for deploying the OpenOffice API to a new server.

## Prerequisites

- A web server with PHP and a MySQL/MariaDB database (like Apache or Nginx).
- FTP/SFTP access to the server or Git installed.
- A database management tool like phpMyAdmin (optional, but helpful).

---

## Step 1: Transfer Project Files

1.  Copy the entire `op-api` project directory to your server's web root (e.g., `/var/www/html` or `public_html`).
2.  **Important**: Ensure that hidden files like `.htaccess` are included in the transfer.

---

## Step 2: Set Up the Database

1.  **Create a New Database**: On your server, create a new, empty MySQL database (e.g., `openoffice_prod_db`).
2.  **Create a Database User**: Create a new database user (e.g., `openoffice_user`) and assign it a strong, unique password.
3.  **Grant Permissions**: Grant the new user full privileges on the new database.
4.  **Import Data**: Use a database tool (like phpMyAdmin) or the command line to import the `rebuild_database.sql` file into your new database. This will create all the necessary tables, roles, and default data.

---

## Step 3: Configure the Application

This is the most critical step. Open the `config.php` file on the new server and update the following settings:

### 1. Database Connection

Update the `$config_db` array with your new database credentials:

```php
$config_db  = array(
    'host'     => 'localhost',            // Or your database host
    'user'     => 'openoffice_user',      // Your new database username
    'password' => 'YourStrongPasswordHere', // Your new database password
    'database' => 'openoffice_prod_db',   // Your new database name
    'port'     => 3306
);
```

### 2. Application URL

Update the `app_url` in the `$app_config` array to your server's public domain name:

```php
$app_config = array(
    // ... other settings
    'app_url' => 'https://your-new-domain.com/',
    'app_debug' => false // Recommended for production
);
```

### 3. Production Settings (Highly Recommended)

For security and performance, make these changes for a live environment:

- **Disable Debug Mode**: In the `$app_config` array, set `'app_debug' => false`.
- **Enable Secure Cookies**: If your site uses HTTPS (which it should), find and uncomment the following line to ensure session cookies are only sent over a secure connection:

  ```php
  // Change this:
  // ini_set('session.cookie_secure', 1);

  // To this:
  ini_set('session.cookie_secure', 1);
  ```

---

## Step 4: Final Checks

- Verify that your server has `mod_rewrite` (for Apache) enabled so the `.htaccess` file works correctly.
- Test the API by making a request to your new URL (e.g., `https://your-new-domain.com/user`). You should receive a `401 Unauthorized` error, which is expected.
- Test the login endpoint to ensure the database connection is working and you can successfully authenticate.

Your API should now be successfully deployed!
