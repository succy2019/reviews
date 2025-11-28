# Email Notification Setup Guide

## Quick Setup

### 1. Configure Email Settings

Open `config.php` and update these settings:

```php
// Your admin email address (where notifications will be sent)
define('ADMIN_EMAIL', 'your-email@example.com');

// Email sender address (must be valid for your server)
define('EMAIL_FROM', 'noreply@yourwebsite.com');

// Website URL (for links in the email)
define('SITE_URL', 'http://localhost/pop');

// Enable/disable notifications
define('EMAIL_NOTIFICATIONS_ENABLED', true);
```

### 2. For XAMPP Users

XAMPP doesn't send emails by default. You have 3 options:

#### Option A: Use a Mail Service (Recommended)
Install PHPMailer for better email delivery:
```bash
# Download PHPMailer or use Composer
composer require phpmailer/phpmailer
```

#### Option B: Configure sendmail (Windows)
1. Download and install a mail server like Mercury Mail
2. Configure in `php.ini`

#### Option C: Use a Remote SMTP Server
Configure XAMPP to use Gmail/Outlook SMTP (requires PHPMailer)

### 3. Test the Email

1. Submit a test review on your website
2. Check your admin email inbox
3. You should receive a notification with:
   - Reviewer name
   - Star rating
   - Review comment
   - Direct link to admin panel
   - Review ID

## Email Features

✅ Beautiful HTML email template
✅ Star rating display
✅ Review details (name, comment, date)
✅ Direct link to admin panel
✅ Automatic notification on new review submission
✅ Can enable/disable in config
✅ Error logging

## For Production (Live Website)

1. Update `SITE_URL` to your actual domain
2. Use a real email address for `EMAIL_FROM`
3. Ensure your web host supports PHP `mail()` function
4. Consider using SMTP for better delivery

## Troubleshooting

**Emails not sending?**
- Check `ADMIN_EMAIL` is correct
- Verify `EMAIL_NOTIFICATIONS_ENABLED` is `true`
- Check PHP error logs
- Test if your server supports `mail()` function

**For better reliability, consider:**
- Using PHPMailer library
- SMTP authentication
- Email service providers (SendGrid, Mailgun, etc.)
