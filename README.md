# Laravel office365 mailer 


Mail driver for Laravel to send emails using the Microsoft Graph API without user authentication and SMTP.

## Installation

You can install the package via composer:

```bash
composer require socialsquared/office365mailer
```

## Configuration

To use this package you need to create an app in the Azure portal and grant the required permissions. Use these [instructions](https://docs.microsoft.com/en-us/graph/auth-v2-service)

- Open the [Azure Active Directory-Portal](https://portal.azure.com/#blade/Microsoft_AAD_IAM/ActiveDirectoryMenuBlade/Overview) with your Office365 Admin account
- Go to `App registrations` and create a new app
- Add the required permissions (Mail.Send, User.Read)
- Apply the Admin-Permission for your organization
- Create a new client secret and save the secret and the client id. You will need it later in your `.env` file

Add the following to your `.env` file:

```text
MAIL_MAILER=office365

OFFICE365MAIL_CLIENT_ID=your-client-id
OFFICE365MAIL_TENANT=your-tenant-id
OFFICE365MAIL_SECRET=your-client-secret
```

Add the following to mailer configuration in your `config/mail.php` file to the `mailers` array:

```php
'office365' => [
    'transport' => 'office365',
    'client_id' => env('OFFICE365MAIL_CLIENT_ID'),
    'tenant' => env('OFFICE365MAIL_TENANT'),
    'client_secret' => env('OFFICE365MAIL_SECRET')
],
```