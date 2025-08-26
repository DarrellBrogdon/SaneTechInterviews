# Sane Tech Interviews

A pledge website for companies to commit to better hiring practices in the tech industry.

## Overview

This website allows companies and hiring managers to take a pledge to reverse the nonsensical interviewing practices that have taken over tech companies. It features a clean, professional design with a comprehensive pledge form and admin interface.

## Technology Stack

- **Frontend**: HTML5, TailwindCSS, Vanilla JavaScript
- **Backend**: PHP
- **Database**: SQLite
- **Fonts**: Inter (Google Fonts)

## Features

- Responsive design that works on all devices
- Comprehensive pledge form with validation
- Real-time pledge counter
- Admin interface to view submissions
- Spam protection (honeypot + rate limiting)
- Duplicate submission prevention
- Client and server-side validation
- Clean, professional UI with TailwindCSS

## File Structure

```
/
├── index.html              # Main website page
├── admin.php              # Admin interface to view pledges
├── process_pledge.php     # Form submission handler
├── get_pledge_count.php   # API endpoint for pledge count
├── js/
│   └── main.js           # Frontend JavaScript
├── database/             # Created automatically
│   ├── pledges.db       # SQLite database (auto-created)
│   └── rate_limits.json # Rate limiting data (auto-created)
└── README.md            # This file
```

## Setup Instructions

### Requirements

- Web server with PHP support (Apache, Nginx, etc.)
- PHP 7.4 or higher
- SQLite support in PHP (usually enabled by default)
- Write permissions for the database directory

### Installation

1. **Upload files** to your web server's document root or a subdirectory
2. **Set permissions** - Ensure the web server can write to the project directory:
   ```bash
   chmod 755 /path/to/project
   chmod -R 755 /path/to/project
   ```
3. **Test the setup** by visiting your domain in a browser
4. The database and required directories will be created automatically on first use

### Local Development

For local development, you can use PHP's built-in server:

```bash
# Navigate to the project directory
cd /path/to/sane-tech-interviews

# Start PHP development server
php -S localhost:8000

# Visit http://localhost:8000 in your browser
```

## Database Schema

The SQLite database (`database/pledges.db`) contains a single table:

```sql
CREATE TABLE pledges (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    company_name TEXT NOT NULL,
    contact_name TEXT NOT NULL,
    email TEXT NOT NULL,
    title TEXT,
    website TEXT,
    pledge_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    ip_address TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
```

## API Endpoints

### POST /process_pledge.php
Processes pledge form submissions.

**Parameters:**
- `company_name` (required): Company name
- `contact_name` (required): Contact person's name
- `email` (required): Email address
- `title` (optional): Job title
- `website` (optional): Company website
- `agree` (required): Agreement checkbox
- `honeypot` (hidden): Spam protection field

**Response:**
```json
{
    "success": true|false,
    "message": "Success or error message"
}
```

### GET /get_pledge_count.php
Returns the total number of pledges.

**Response:**
```json
{
    "success": true,
    "data": {
        "count": 42
    }
}
```

## Security Features

- **Input Validation**: Both client-side and server-side validation
- **SQL Injection Protection**: Prepared statements used throughout
- **XSS Protection**: All output is properly escaped
- **Rate Limiting**: Maximum 5 submissions per hour per IP address
- **Spam Protection**: Honeypot field to catch bots
- **Duplicate Prevention**: Prevents multiple submissions from same email/company

## Admin Interface

Access the admin interface at `/admin.php` to view:
- All pledge submissions in a sortable table
- Summary statistics (total, monthly, weekly counts)
- Contact information for follow-up

### Admin Authentication

The admin interface is protected with HTTP Basic Authentication:

**Default Credentials:**
- Username: `admin`
- Password: `sane2025!`

**Important Security Notes:**
1. **Change the default password** before deploying to production
2. Edit `admin-config.php` to update credentials:
   ```php
   return [
       'username' => 'your_username',
       'password' => 'your_secure_password',
       'realm' => 'Sane Tech Interviews Admin'
   ];
   ```
3. Ensure `admin-config.php` has proper file permissions (readable by web server only)
4. Consider using environment variables for credentials in production
5. Enable HTTPS to protect credentials in transit

### Accessing Admin Panel

1. Navigate to `/admin.php` in your browser
2. Enter the username and password when prompted
3. If credentials are incorrect, you'll see a styled "Access Denied" page

## Customization

### Styling
The website uses TailwindCSS via CDN. To customize:
1. Modify the HTML classes in `index.html`
2. Add custom CSS in the `<style>` section
3. Or create a separate CSS file

### Content
To modify the pledge content:
1. Edit the pledge text in `index.html`
2. Update the form fields as needed
3. Modify validation rules in both `js/main.js` and `process_pledge.php`

### Database
To add new fields:
1. Update the database schema in `process_pledge.php`
2. Add form fields to `index.html`
3. Update validation in both frontend and backend
4. Modify the admin interface in `admin.php`

## Deployment

### Production Checklist

1. **Security**:
   - Protect `admin.php` with authentication
   - Set proper file permissions
   - Enable HTTPS
   - Configure proper error logging

2. **Performance**:
   - Enable gzip compression
   - Set up proper caching headers
   - Consider using a CDN for static assets

3. **Monitoring**:
   - Set up error logging
   - Monitor database size
   - Track submission rates

4. **Backup**:
   - Regular database backups
   - File system backups

## Troubleshooting

### Common Issues

1. **Database not created**: Check write permissions on the project directory
2. **Form not submitting**: Check PHP error logs, ensure all required fields are present
3. **Count not loading**: Verify `get_pledge_count.php` is accessible and database exists
4. **Rate limiting issues**: Check `database/rate_limits.json` file permissions

### Error Logs

Check your web server's error logs for PHP errors:
- Apache: Usually in `/var/log/apache2/error.log`
- Nginx: Usually in `/var/log/nginx/error.log`
- PHP: Check `php.ini` for `error_log` location

## License

This project is open source. Feel free to modify and use for your own purposes.

## Support

For issues or questions, please check the troubleshooting section above or review the code comments for implementation details.
