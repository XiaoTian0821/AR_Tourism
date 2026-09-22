# AR Tourism Explorer

A complete Web-Based Augmented Reality (AR) Tourism & Cultural Heritage Information System built with PHP, MySQL, and MindAR.

## Features

- 🏛️ **Public Website**: Modern tourism portal with destinations, attractions, and information
- 🔍 **AR Experience**: Scan tourism posters to reveal interactive AR content
- 🎥 **Video Integration**: YouTube video support in AR overlays
- 🗺️ **Map Integration**: Google Maps integration for attraction locations
- 📱 **Mobile-First**: Responsive design optimized for smartphones
- 🔐 **Admin Panel**: Secure CMS for managing all content
- 🖼️ **Visual Hotspot Editor**: Drag-and-drop hotspot configuration
- 📊 **Activity Logs**: Track all administrative actions
- 🔒 **Security**: CSRF protection, prepared statements, input sanitization

## Technology Stack

### Backend
- PHP 8.3+
- MySQL 8+
- PDO for database access
- REST-style endpoints

### Frontend
- HTML5
- CSS3
- Vanilla JavaScript
- Bootstrap 5
- Font Awesome
- Google Fonts
- MindAR Image Tracking
- Three.js

## Requirements

- XAMPP (Apache + MySQL + PHP 8.3+)
- Web browser with HTTPS support
- Smartphone camera for AR experience
- Modern browsers: Chrome, Safari, Edge

## Installation

### 1. Copy Project
Copy the `ar-tourism` folder to your XAMPP `htdocs` directory:
```
C:\xampp\htdocs\AR_Tourism
```

### 2. Create Database
1. Start XAMPP (Apache + MySQL)
2. Open phpMyAdmin: http://localhost/phpmyadmin
3. Create a new database named `ar_tourism`
4. Import the SQL file: `database/database.sql`

### 3. Configure Database
Edit `includes/config.php` and update database credentials if needed:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'ar_tourism');
define('DB_USER', 'root');
define('DB_PASS', '');
```

### 4. Set Permissions
Ensure the following directories are writable:
```
assets/uploads/
assets/ar-targets/
```

### 5. Access the System
- Public site: http://localhost/AR_Tourism/
- Admin login: http://localhost/AR_Tourism/admin/login.php

**Default Admin Account:**
- Username: `admin`
- Password: `admin123`

## HTTPS Setup (Required for AR)

Camera access requires HTTPS. For local development:

### Option 1: XAMPP Self-Signed Certificate
1. Generate a self-signed certificate for localhost
2. Configure Apache to use HTTPS
3. Import the certificate into your browser

### Option 2: Ngrok (Recommended for Testing)
```bash
ngrok http 80
```
Use the HTTPS URL provided by Ngrok for AR testing.

### Option 3: Production
Deploy to an HTTPS-enabled hosting provider.

## Admin Usage

### Dashboard
- View statistics and recent activity
- Quick access to all management sections

### Manage Destinations
- Add/edit/delete tourist destinations
- Set locations and map links
- Upload cover images

### Manage Attractions
- Create attractions under destinations
- Upload multiple images (gallery)
- Add YouTube videos
- Set opening hours and entry information

### AR Poster Management
1. Create a poster and upload the image
2. Configure hotspots visually
3. Assign attractions to hotspots
4. Compile the MindAR target
5. Preview and test the AR experience

### Hotspot Editor
- Click on the poster to add hotspots
- Drag hotspots to reposition
- Resize hotspots as needed
- Assign attractions or videos
- Save and compile

## Visitor Usage

1. Visit the website on a smartphone
2. Browse destinations and attractions
3. Tap "AR Experience" button
4. Allow camera permission
5. Point camera at the tourism poster
6. Tap detected hotspots to view information
7. Watch videos and open maps

## AR Target Compilation

The system uses MindAR Image Tracking. To compile a target:

1. Upload a high-quality poster image (JPEG/PNG, min 1000px)
2. Add hotspots to the poster
3. Click "Compile Target"
4. Wait for the .mind file to generate
5. Test with the AR experience page

**Note**: Target compilation requires the MindAR compiler tool. For a student project, you can manually compile targets using the [MindAR Studio](https://hiukim.github.io/mind-ar-js-docs/tools/mindar-image) or use pre-compiled targets for demonstration.

## File Structure

```
AR_Tourism/
├── index.php                    # Homepage
├── ar.php                       # AR Experience page
├── destinations.php             # Destinations list
├── attraction.php               # Attraction detail
├── about.php                    # About page
├── contact.php                  # Contact page
│
├── admin/
│   ├── index.php                # Admin dashboard
│   ├── login.php                # Admin login
│   ├── logout.php               # Admin logout
│   ├── destinations/            # Destination management
│   ├── attractions/             # Attraction management
│   ├── categories/              # Category management
│   ├── ar-posters/              # AR poster management
│   ├── users/                   # User management
│   ├── logs/                    # Activity logs
│   └── settings/                # System settings
│
├── api/                         # REST API endpoints
│   ├── ar/                      # AR data endpoints
│   ├── destinations/            # Destination API
│   └── attractions/             # Attraction API
│
├── assets/
│   ├── css/                     # Stylesheets
│   ├── js/                      # JavaScript files
│   ├── images/                  # Public images
│   ├── uploads/                 # Uploaded files
│   └── ar-targets/              # AR target files
│
├── includes/
│   ├── config.php               # Configuration
│   ├── database.php             # Database connection
│   ├── auth.php                 # Authentication
│   ├── functions.php            # Helper functions
│   ├── header.php               # Header template
│   └── footer.php               # Footer template
│
├── ar/
│   ├── js/                      # AR JavaScript
│   └── components/              # AR components
│
├── database/
│   └── database.sql             # Database schema
│
└── README.md                    # This file
```

## Security Features

- ✅ PDO prepared statements (SQL injection prevention)
- ✅ Password hashing with `password_hash()`
- ✅ CSRF token protection
- ✅ XSS output escaping with `htmlspecialchars()`
- ✅ File upload validation (MIME type, extension, size)
- ✅ Session-based authentication
- ✅ Admin authorization checks
- ✅ Input sanitization
- ✅ Secure .htaccess rules

## Troubleshooting

### Camera Not Working
- Ensure HTTPS is enabled
- Check browser permissions
- Try a different browser (Chrome recommended)
- Clear browser cache

### AR Target Not Detected
- Ensure poster image is high quality
- Check lighting conditions
- Make sure entire poster is visible
- Verify target is compiled and uploaded

### Admin Login Issues
- Clear browser cookies
- Reset password in database
- Check session configuration
- Verify database connection

### Upload Errors
- Check directory permissions
- Verify PHP upload limits in php.ini
- Check file size limits
- Ensure MIME type is supported

## Deployment to cPanel

1. Upload all files to public_html
2. Create MySQL database and user
3. Import database.sql via phpMyAdmin
4. Update includes/config.php with database credentials
5. Set proper permissions on upload directories
6. Configure HTTPS in cPanel
7. Test all functionality

## Browser Compatibility

| Browser | Desktop | Mobile | AR Support |
|---------|---------|--------|------------|
| Chrome  | ✅      | ✅     | ✅         |
| Safari  | ✅      | ✅     | ✅         |
| Edge    | ✅      | ✅     | ✅         |
| Firefox | ✅      | ✅     | ⚠️ Limited |

**Note**: AR functionality requires modern mobile browsers with WebXR support. Chrome on Android and Safari on iOS are recommended.

## Demo Data

The system includes demo data for:
- **Heritage District**: Kek Lok Si Temple, Cultural sites
- **Nature Escape**: Penang Hill, Botanical gardens
- **Coastal Destination**: Penang Bridge, Beaches
- **Food Heritage Area**: Local food streets, Hawker centers

## License

This project is for educational purposes and can be used for academic projects, final-year projects, and demonstrations.

## Support

For issues or questions:
1. Check the troubleshooting section
2. Review the code comments
3. Test with demo data first
4. Check browser console for errors
