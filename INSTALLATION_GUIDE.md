# 🚀 e-Governance Complaint Portal - Installation Guide (Windows)

## 📦 What You Have

You now have a complete, production-ready WordPress plugin with:

✅ **26 Files Created**
- Core plugin functionality
- Database management
- User role system
- Admin control room
- Officer dashboard
- Citizen complaint forms
- Ultimate Member integration
- Complete styling (CSS)
- JavaScript interactions

---

## 🔧 Step-by-Step Installation (Windows)

### Step 1: Locate Your WordPress Installation

Your WordPress is typically installed at:
```
C:\xampp\htdocs\your-site\
```
or
```
C:\wamp64\www\your-site\
```

### Step 2: Copy Plugin Files

1. **Navigate** to your WordPress plugins folder:
   ```
   C:\xampp\htdocs\your-site\wp-content\plugins\
   ```

2. **Create** a new folder named:
   ```
   e-governance-complaint-portal
   ```

3. **Copy** all files from your download into this folder

Your final structure should look like:
```
wp-content\plugins\e-governance-complaint-portal\
├── e-governance-complaint-portal.php
├── README.md
├── PLAN.md
├── uninstall.php
├── assets\
│   ├── css\
│   │   ├── admin-style.css
│   │   └── public-style.css
│   └── js\
│       ├── admin-script.js
│       └── public-script.js
├── includes\
│   ├── class-egcp-activator.php
│   ├── class-egcp-complaint.php
│   ├── class-egcp-database.php
│   ├── class-egcp-deactivator.php
│   ├── class-egcp-roles.php
│   ├── class-egcp-sla.php
│   └── class-egcp-um-integration.php
├── admin\
│   ├── class-egcp-admin-menu.php
│   ├── class-egcp-control-room.php
│   ├── class-egcp-officer-dashboard.php
│   └── views\
│       ├── control-room.php
│       └── officer-dashboard.php
└── public\
    ├── class-egcp-shortcodes.php
    └── views\
        ├── complaint-form.php
        ├── complaint-details.php
        ├── my-complaints.php
        ├── replies.php
        └── helpdesk.php
```

### Step 3: Activate the Plugin

1. **Log in** to WordPress Admin:
   ```
   http://localhost/your-site/wp-admin
   ```

2. **Navigate** to: Plugins → Installed Plugins

3. **Find** "e-Governance Complaint Portal"

4. **Click** "Activate"

### Step 4: Verify Installation

After activation, you should see:

✅ New menu item: **"Complaints"** in WordPress admin sidebar
✅ Success message: "Plugin activated"
✅ No errors displayed

### Step 5: Check Database Tables

Run this SQL query in phpMyAdmin to verify tables were created:

```sql
SHOW TABLES LIKE 'wp_egcp_%';
```

You should see 3 tables:
- `wp_egcp_complaints`
- `wp_egcp_replies`
- `wp_egcp_status_history`

---

## ⚙️ Initial Configuration

### 1. Configure Plugin Settings

**Navigate:** WordPress Admin → Complaints → Settings

**Configure:**
- ✅ Enable complaint submissions
- ✅ Set allowed departments (Health, Water, Electricity)
- ✅ Adjust SLA periods if needed
- ✅ Configure email notifications

### 2. Create Officer Accounts

**Method A: Create New Users**

1. Go to: Users → Add New
2. Create user with these details:
   - Username: `health.officer`
   - Email: `health@yourdomain.com`
   - Role: **Subscriber** (we'll change this next)

**Method B: Assign Officer Roles**

1. Go to: Complaints → Officers
2. Select existing user
3. Assign department role(s):
   - Health Officer
   - Water Officer
   - Electricity Officer

### 3. Test Citizen Functionality

**Requirements:**
- Ultimate Member plugin must be installed and activated
- You need a test citizen account (WordPress Subscriber)

**Testing Steps:**

1. **Log in** as a subscriber/citizen
2. **Navigate** to your profile (Ultimate Member account page)
3. **You should see** 5 new tabs:
   - File New Complaint
   - My Complaints
   - Complaint Tracking
   - Officer Replies
   - Helpdesk

4. **Test complaint submission:**
   - Click "File New Complaint"
   - Fill out the form
   - Submit
   - Note the Grievance ID

### 4. Test Officer Dashboard

1. **Log out** from citizen account
2. **Log in** as an officer account
3. **Navigate** to: WordPress Admin → My Department
4. **You should see:**
   - Department statistics
   - Pending complaints (0 if none assigned)
   - SLA metrics

### 5. Test Admin Control Room

1. **Log in** as WordPress Administrator
2. **Navigate** to: Complaints → Control Room
3. **You should see:**
   - Dashboard with metrics
   - Recent complaints
   - Export functionality

---

## 🎯 Quick Start Usage

### For Citizens

**File a Complaint:**
1. Log in to your account
2. Go to your profile
3. Click "File New Complaint" tab
4. Fill out:
   - Title (10-200 chars)
   - Description (min 50 chars)
   - Department
   - Location (State, District, Pincode)
   - Upload images (optional, max 5)
5. Submit
6. Save your Grievance ID

**Track Complaint:**
- Use "My Complaints" tab to see all submissions
- Use "Complaint Tracking" to search by Grievance ID

### For Officers

**Manage Complaints:**
1. Log in to WordPress admin
2. Go to "My Department"
3. View assigned complaints
4. Click "View Details"
5. Update status or add reply

### For Admins

**Assign Complaints:**
1. Go to Control Room or All Complaints
2. Find pending complaint
3. Assign to appropriate officer
4. Add admin reply if needed

**Export Data:**
1. Go to Control Room
2. Scroll to Export section
3. Set filters (department, status, date range)
4. Click "Export to CSV"

---

## 🐛 Common Issues & Solutions

### Issue 1: Plugin Activation Error

**Error:** "The plugin does not have a valid header."

**Solution:**
- Ensure `e-governance-complaint-portal.php` is in the root of the plugin folder
- Check file encoding (should be UTF-8 without BOM)

### Issue 2: Database Tables Not Created

**Error:** Complaints not saving

**Solution:**
1. Deactivate plugin
2. Check database permissions
3. Reactivate plugin
4. Check error logs in: `wp-content/debug.log`

### Issue 3: Ultimate Member Tabs Not Showing

**Solution:**
- Ensure Ultimate Member is activated
- Clear WordPress cache
- Check if viewing own profile (tabs only show on own profile)

### Issue 4: Images Not Uploading

**Solutions:**
- Check file size (max 2MB)
- Verify format (JPG, PNG, WebP only)
- Check PHP upload limits:
  - `upload_max_filesize = 10M`
  - `post_max_size = 10M`
- Verify folder permissions for `wp-content/uploads/egcp-complaints/`

### Issue 5: Styles Not Loading

**Solution:**
1. Hard refresh browser (Ctrl + F5)
2. Clear WordPress cache
3. Check file paths in browser developer tools
4. Verify CSS files are in: `assets/css/`

---

## 📊 Testing Checklist

### Citizen Tests
- [ ] Submit complaint with all fields
- [ ] Submit complaint with images
- [ ] View complaint in "My Complaints"
- [ ] Track complaint by Grievance ID
- [ ] View officer/admin replies

### Officer Tests
- [ ] View assigned complaints
- [ ] Update complaint status
- [ ] Add officer reply
- [ ] Verify cannot delete complaints

### Admin Tests
- [ ] View Control Room metrics
- [ ] Assign complaint to officer
- [ ] Add admin reply
- [ ] Update any status
- [ ] Export complaints to CSV
- [ ] Delete complaint

---

## 🔐 Security Checklist

After installation, verify:

- [ ] Only logged-in users can submit complaints
- [ ] Officers can only see their department complaints
- [ ] Officers cannot delete complaints
- [ ] Only admins can assign roles
- [ ] Only admins can export data
- [ ] File uploads are validated
- [ ] All forms use nonces

---

## 📚 Next Steps

### 1. Customize State/District Lists

**Location:** Complaints → Settings → General Settings

Edit the states array to match your region.

### 2. Adjust SLA Periods

**Location:** Complaints → Settings → SLA Settings

Change default periods:
- Health: 7 days
- Water: 15 days
- Electricity: 10 days

### 3. Set Up Email Notifications

**Location:** Complaints → Settings → Email Notifications

Configure:
- Notify citizens on status change
- Notify officers on assignment
- Admin email for escalations

### 4. Add Custom Departments

To add more departments (e.g., "roads", "sanitation"):

1. Edit `includes/class-egcp-roles.php`
2. Add new role:
   ```php
   add_role(
       'roads_officer',
       __( 'Roads Officer', 'e-governance-complaint-portal' ),
       $officer_capabilities
   );
   ```
3. Update department arrays throughout the code
4. Add to settings: `egcp_allowed_departments`

### 5. Backup Your Data

**Recommended:** Set up automated backups

**Manual Backup:**
```sql
-- Export complaints table
SELECT * FROM wp_egcp_complaints 
INTO OUTFILE 'complaints_backup.csv';
```

---

## 🎓 Training Materials

### For Citizens
- Read the "Helpdesk" tab in your profile
- Watch for email notifications
- Save your Grievance ID

### For Officers
- Review SLA compliance daily
- Update status promptly
- Add detailed replies

### For Admins
- Monitor Control Room metrics
- Assign complaints fairly
- Export data monthly for reports

---

## 📞 Support

If you encounter issues:

1. **Check Logs:**
   - WordPress debug log: `wp-content/debug.log`
   - PHP error log (ask your hosting provider)

2. **Enable Debug Mode:**
   Add to `wp-config.php`:
   ```php
   define('WP_DEBUG', true);
   define('WP_DEBUG_LOG', true);
   define('WP_DEBUG_DISPLAY', false);
   ```

3. **Contact Support:**
   - Review PLAN.md for technical details
   - Check README.md for documentation

---

## ✅ Installation Complete!

Your e-Governance Complaint Portal is now ready to use.

**What's Working:**
✅ Citizen complaint submission
✅ Officer dashboard with department filtering
✅ Admin control room with metrics
✅ SLA tracking (7/15/10 days by department)
✅ Role-based access control
✅ Image uploads (max 5 per complaint)
✅ CSV export for RTI/audit
✅ Ultimate Member integration
✅ Status tracking and replies

**Remember:**
- Test thoroughly before going live
- Customize settings to your needs
- Train your officers and admins
- Set up regular backups

---

**Need Help?** Review the README.md and PLAN.md files for detailed documentation.

**Happy Governance! 🏛️**