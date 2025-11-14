# Troubleshooting Guide - Asterisk ARI Dialer

## Issue: Numbers Not Showing After Adding

### What I Fixed:
✅ **Enhanced the "Add Numbers" form with:**
- Better loading indicators (spinning icon)
- Clear success messages
- Force page reload from server
- Number count badge in header
- Helpful empty state message

### How to Add Numbers:

#### Method 1: Bulk Add (Recommended)

1. Go to **Campaigns** → Click on your campaign
2. Scroll to "Add Numbers (Bulk)" section
3. Enter numbers in the format:
   ```
   1234567890,John Doe
   9876543210,Jane Smith
   5555555555
   ```
   (Name is optional - just number also works)

4. Click "Add Numbers" button
5. You'll see a message: "✓ X number(s) added successfully"
6. Page will automatically reload
7. Check the "Campaign Numbers" section - you'll see a badge showing count

#### Method 2: CSV Upload

1. Create a CSV file:
   ```csv
   1234567890,John Doe
   9876543210,Jane Smith
   5555555555
   ```

2. Use "Upload Numbers" → Select CSV file → Click Upload
3. Page will reload automatically

### Verify Numbers Were Added:

1. **Visual Check:**
   - Look at the badge in "Campaign Numbers" header
   - Should show "X numbers" (e.g., "3 numbers")

2. **Database Check:**
   ```bash
   mysql -u root -pmahapharata adialer -e "SELECT * FROM campaign_numbers WHERE campaign_id = 1;"
   ```

3. **Stats Check:**
   - Look at campaign stats boxes (Total/Pending/Completed)
   - Total should match number count

### Current Status:

I've verified the system is working:
- ✅ Database: Numbers are being inserted
- ✅ Controller: add_numbers_bulk endpoint returns success
- ✅ View: Numbers display correctly in the table
- ✅ Names: Contact names show in "Name" column

### Test Data Already Added:

Your campaign (ID: 1) currently has:
```
1. 1234567890 - Test User
2. 9876543210 - Jane Doe
3. 5555555555 - (no name)
```

### If Numbers Still Don't Show:

1. **Clear browser cache:**
   - Press `Ctrl + F5` (Windows/Linux) or `Cmd + Shift + R` (Mac)
   - This forces a fresh page load

2. **Check browser console:**
   - Press `F12` → Console tab
   - Look for any JavaScript errors

3. **Check PHP errors:**
   ```bash
   tail -f /var/log/httpd/error_log
   ```

4. **Verify database connection:**
   ```bash
   mysql -u root -pmahapharata adialer -e "SELECT COUNT(*) as total FROM campaign_numbers WHERE campaign_id = 1;"
   ```

### Common Issues:

**Issue:** Form submits but nothing happens
- **Solution:** Check browser console for JavaScript errors
- Make sure jQuery is loaded (check page source)

**Issue:** Numbers added but don't display
- **Solution:** Force refresh with `Ctrl + F5`
- Check if viewing correct campaign ID

**Issue:** "Failed to add numbers" error
- **Solution:** Check Apache error log
- Verify database connection in config

### Debug Mode:

Enable debug in CodeIgniter:
```php
// /var/www/html/adial/index.php
define('ENVIRONMENT', 'development');
```

This will show detailed PHP errors.

### Contact/Support:

If issues persist:
1. Check `/var/www/html/adial/logs/` for errors
2. Check Apache logs: `/var/log/httpd/error_log`
3. Check database: Numbers should be in `campaign_numbers` table
4. Test with curl:
   ```bash
   curl -X POST "http://localhost/adial/campaigns/add_numbers_bulk/1" \
        -d "numbers_bulk=1111111111,Test"
   ```

## Quick Test Script:

Save as `test-add.sh`:
```bash
#!/bin/bash
echo "Testing number add functionality..."

# Add test numbers
curl -X POST "http://localhost/adial/campaigns/add_numbers_bulk/1" \
     -d "numbers_bulk=8888888888,Test%20Person%0A9999999999"

echo ""
echo "Checking database..."
mysql -u root -pmahapharata adialer -e "SELECT id, phone_number, data FROM campaign_numbers WHERE campaign_id = 1;"
```

Run with: `bash test-add.sh`
