# AR Tourism Explorer - Testing Checklist

## Pre-Installation Tests
- [ ] XAMPP installed and running
- [ ] PHP version 8.3+ confirmed
- [ ] MySQL 8+ confirmed
- [ ] Apache module mod_rewrite enabled

## Database Tests
- [ ] Database `ar_tourism` created
- [ ] database.sql imported successfully
- [ ] demo-data.sql imported successfully
- [ ] Admin user created (admin/admin123)
- [ ] All tables created with correct structure
- [ ] Foreign keys working properly

## Public Website Tests
- [ ] Homepage loads correctly (index.php)
- [ ] Navigation menu works
- [ ] Destinations page loads (destinations.php)
- [ ] Search functionality works
- [ ] Category filtering works
- [ ] Pagination works
- [ ] Destination detail page works
- [ ] Attractions page loads (attractions.php)
- [ ] Attraction detail page works
- [ ] About page loads
- [ ] Contact page loads and submits
- [ ] AR Experience page loads (ar.php)
- [ ] Footer displays correctly
- [ ] Mobile responsive on all pages

## Admin Authentication Tests
- [ ] Admin login page loads
- [ ] Login with valid credentials works
- [ ] Login with invalid credentials shows error
- [ ] Login with wrong password shows error
- [ ] Session persists after login
- [ ] Session expires after logout
- [ ] Unauthorized access redirects to login
- [ ] CSRF tokens work correctly
- [ ] Remember me functionality works

## Admin Dashboard Tests
- [ ] Dashboard loads with statistics
- [ ] Quick action buttons work
- [ ] Recent attractions display
- [ ] Recent AR posters display
- [ ] Activity logs display
- [ ] Sidebar navigation works
- [ ] Mobile sidebar toggle works

## Category Management Tests
- [ ] List all categories
- [ ] Add new category
- [ ] Edit category
- [ ] Delete category
- [ ] Toggle category status
- [ ] Search categories
- [ ] CSRF protection on forms

## Destination Management Tests
- [ ] List all destinations
- [ ] Add new destination
- [ ] Edit destination
- [ ] Delete destination
- [ ] Toggle destination status
- [ ] Search destinations
- [ ] Filter by category
- [ ] Image upload works
- [ ] Pagination works
- [ ] Database security (SQL injection tested)

## Attraction Management Tests
- [ ] List all attractions
- [ ] Add new attraction
- [ ] Edit attraction
- [ ] Delete attraction
- [ ] Toggle attraction status
- [ ] Search attractions
- [ ] Filter by destination
- [ ] Image upload works
- [ ] YouTube URL validation
- [ ] Google Maps URL validation
- [ ] Gallery image management

## AR Poster Management Tests
- [ ] List all AR posters
- [ ] Add new AR poster
- [ ] Upload poster image
- [ ] Edit AR poster
- [ ] Delete AR poster
- [ ] Compile AR target (simulation)
- [ ] Toggle poster status
- [ ] Search posters
- [ ] Target status tracking

## Hotspot Editor Tests
- [ ] Load hotspot editor
- [ ] Display poster image
- [ ] Add new hotspot
- [ ] Select existing hotspot
- [ ] Edit hotspot properties
- [ ] Delete hotspot
- [ ] Assign attraction to hotspot
- [ ] Save hotspot positions
- [ ] Normalized coordinate storage

## API Tests
- [ ] GET /api/ar/poster.php - returns poster data
- [ ] GET /api/ar/hotspots.php - returns hotspot data
- [ ] GET /api/destinations/ - returns destinations
- [ ] GET /api/attractions/ - returns attractions
- [ ] All APIs return valid JSON
- [ ] CORS headers present
- [ ] Error handling works

## Security Tests
- [ ] SQL injection prevented (PDO prepared statements)
- [ ] XSS protected (htmlspecialchars output escaping)
- [ ] CSRF tokens validated
- [ ] Passwords hashed (password_hash)
- [ ] Session hijacking prevented
- [ ] File upload validation works
- [ ] MIME type checking works
- [ ] File extension whitelist enforced
- [ ] PHP execution disabled in upload directories
- [ ] Direct file access blocked via .htaccess
- [ ] No sensitive data in JavaScript
- [ ] Admin pages require authentication

## AR Experience Tests
- [ ] AR page loads
- [ ] HTTPS warning displays on non-HTTPS
- [ ] Camera permission request works
- [ ] Poster selection works
- [ ] Hotspot display works
- [ ] Information card displays
- [ ] Video integration works (YouTube)
- [ ] Map integration works (Google Maps)
- [ ] Back button works
- [ ] Error messages display correctly
- [ ] Loading states work

## Responsive Design Tests
- [ ] Desktop (1920x1080) - all elements visible
- [ ] Laptop (1366x768) - all elements visible
- [ ] Tablet (768x1024) - layout adapts
- [ ] Mobile (375x667) - layout adapts
- [ ] Touch targets are large enough (44x44px min)
- [ ] Text is readable at all sizes
- [ ] Images scale correctly
- [ ] Forms are usable on mobile
- [ ] Navigation collapses on mobile

## Browser Compatibility Tests
- [ ] Chrome Desktop - all features work
- [ ] Firefox Desktop - all features work
- [ ] Safari Desktop - all features work
- [ ] Edge Desktop - all features work
- [ ] Chrome Android - AR features work
- [ ] Safari iOS - AR features work (limited)
- [ ] Chrome Desktop - AR features test

## Error Handling Tests
- [ ] 404 page for missing destinations
- [ ] 404 page for missing attractions
- [ ] Empty state messages display
- [ ] Form validation errors display
- [ ] Database connection errors handled
- [ ] File upload errors handled
- [ ] Camera permission denied handled
- [ ] HTTPS requirement message displays

## Performance Tests
- [ ] Page load time < 3 seconds
- [ ] Images lazy load correctly
- [ ] Database queries optimized
- [ ] No duplicate queries
- [ ] Static assets cached
- [ ] JavaScript minified where possible

## Documentation Tests
- [ ] README.md complete and accurate
- [ ] Database schema documented
- [ ] Installation instructions clear
- [ ] Admin usage documented
- [ ] Troubleshooting section helpful
- [ ] Code comments present
- [ ] File structure documented

## Demo Data Tests
- [ ] All demo destinations display
- [ ] All demo attractions display
- [ ] Penang attractions correct (Kek Lok Si, Penang Hill, Penang Bridge)
- [ ] Categories include all demo types
- [ ] YouTube URLs valid format
- [ ] Google Maps URLs valid format
- [ ] Coordinates set correctly

## Final Checks
- [ ] All PHP files syntax valid
- [ ] All SQL files syntax valid
- [ ] No broken links on any page
- [ ] No console errors in browser
- [ ] All icons display correctly
- [ ] Colors match design system
- [ ] Typography consistent
- [ ] Spacing consistent
- [ ] Accessibility features work
- [ ] Print styles work
