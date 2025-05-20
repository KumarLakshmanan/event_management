# Setup Instructions

This document provides the setup instructions for the project.

## API Configuration

Update the following variables in your PHP configuration file:
`/includes/config.php`

```php
define('APP_NAME', 'Event Planning Platform');
define('APP_URL', 'http://' . $_SERVER['HTTP_HOST'] . '/event_v3/');
```

## Mail Configuration

Set up your mail credentials:
`/includes/config.php`

```php
define('MAIL_USERNAME', 'your_email@example.com');
define('MAIL_PASSWORD', 'your_password');
```

> **Note:** Replace the email credentials with your actual email details. Do not share sensitive information publicly.

<!-- GOOGLE SMTP -->
> **Note:** If you are using Gmail, ensure you have enabled "Less secure app access" in your Google account settings. Alternatively, consider using OAuth2 for better security.
<!-- END GOOGLE SMTP -->

## SQLite File Generation

Run the `setup.php` file in your browser to generate the SQLite file.

## Steps to Run the Project

1. Clone the repository to your local machine.
2. Run `setup.php` in your browser to generate the SQLite file.
3. Start your local server (e.g., XAMPP or WAMP).
4. Access the application via the extracted path.

## Troubleshooting
- Ensure your local server is running.
- Check for missing dependencies or extensions in your PHP setup.
- Confirm the database files were imported successfully.


## Additional Notes
- Change the project folder name to match your logon code.
