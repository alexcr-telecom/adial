# IVR and Queue Fixes

## Issue #1: Queue Configuration - RESOLVED ✓

**Problem**: User was unclear about how to configure Queue actions in IVR

**Finding**: The code already works correctly! The stasis application (`stasis-app/app.js` lines 644-672) automatically formats queue numbers as `LOCAL/{queue}@from-internal`.

**Solution**:
- Added clear help text in the IVR form
- When selecting "Queue" as action type, you now see: "Enter queue number only (e.g., 600, 701). System will dial LOCAL/{queue}@from-internal"
- Just enter the queue number (e.g., "600" or "701"), not the full path

**Example**:
- ✓ Correct: Enter "600" in the action value field
- ✗ Wrong: Enter "LOCAL/600@from-internal" (the system adds this automatically)

---

## Issue #2: IVR DTMF Actions Not Saving - FIXED ✓

**Problem**: When adding multiple DTMF actions at once, they wouldn't all save. User had to add them one by one.

**Root Cause**:
1. Database allowed duplicate DTMF digits within the same IVR menu
2. No client-side validation to prevent duplicates
3. No error handling to report failures

**Solutions Applied**:

### 1. Database Fix
- Added UNIQUE constraint on `ivr_actions` table: `(ivr_menu_id, dtmf_digit)`
- This prevents duplicate DTMF digits within the same IVR menu
- Automatically removed any existing duplicates

```sql
ALTER TABLE ivr_actions
ADD UNIQUE KEY unique_menu_dtmf (ivr_menu_id, dtmf_digit);
```

### 2. Client-Side Validation
- Added JavaScript validation to detect duplicate DTMF digits before submission
- Shows clear error message: "Duplicate DTMF digit detected: {digit}"
- Form won't submit until duplicates are removed

### 3. Server-Side Improvements
- Enhanced `save_actions()` method with proper error handling
- Now logs errors when actions fail to save
- Shows warning message if some actions couldn't be saved
- Properly handles hangup actions (which don't need action_value)

---

## Files Modified

1. **Database Schema**:
   - `fix_ivr_unique_constraint.sql` - New file with database fix
   - Applied to `ivr_actions` table

2. **Views**:
   - `application/views/ivr/form.php`:
     - Added queue help text
     - Added JavaScript validation for duplicate DTMF digits
     - Improved action type change handler

3. **Controllers**:
   - `application/controllers/Ivr.php`:
     - Improved `save_actions()` method with error handling
     - Added try-catch blocks
     - Added logging for failures
     - Fixed hangup action handling

4. **Language Files**:
   - `application/language/english/ivr_lang.php`:
     - Added `ivr_error_duplicate_dtmf`
     - Added `ivr_error_duplicate_dtmf_help`
     - Added `ivr_help_queue_number`

---

## Testing the Fixes

### Test Queue Configuration:
1. Create or edit a campaign
2. Set agent destination to "IVR"
3. Create IVR menu with a Queue action
4. In the action value field, enter just the queue number (e.g., "600")
5. Save and test - system should dial LOCAL/600@from-internal

### Test DTMF Duplicate Prevention:
1. Create or edit an IVR menu
2. Try to add two actions with the same DTMF digit (e.g., both "1")
3. You should see an error: "Duplicate DTMF digit detected"
4. Remove or change one of the duplicates
5. Form should submit successfully

### Test Multiple DTMF Actions:
1. Create a new IVR menu
2. Add multiple DTMF actions with DIFFERENT digits (e.g., 1, 2, 3, *, #, i, t)
3. Click "Create IVR Menu"
4. All actions should save successfully
5. View the IVR menu to verify all actions are present

---

## Database Verification

To verify the UNIQUE constraint was applied:

```bash
mysql -u adialer_user -piCyrq0ghonj2sWzD adialer -e "SHOW CREATE TABLE ivr_actions\G"
```

You should see:
```
UNIQUE KEY `unique_menu_dtmf` (`ivr_menu_id`,`dtmf_digit`)
```

---

## Summary

Both issues are now resolved:
1. ✓ Queue configuration works correctly - added clear documentation
2. ✓ IVR DTMF actions now save properly - fixed database constraint and added validation
3. ✓ Better error handling and user feedback
4. ✓ Help text added to guide users

The system now prevents the issues you encountered and provides clear feedback when something goes wrong.
