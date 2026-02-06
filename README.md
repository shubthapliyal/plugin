# e-Governance Complaint Portal

A comprehensive WordPress plugin for managing government complaints with role-based access control, SLA tracking, and Ultimate Member integration.

## 📋 Features

### For Citizens
- ✅ Submit complaints via frontend form
- 📸 Upload up to 5 images per complaint
- 🔍 Track complaints using Grievance ID
- 📊 View complaint status and SLA timer
- 💬 Receive officer and admin replies
- 📱 Ultimate Member profile integration

### For Officers
- 👨‍💼 Department-specific dashboard
- ⏱️ SLA timer visibility
- ✏️ Update complaint status (In Progress, Resolved, Escalated)
- 💬 Add internal/technical replies
- 🚫 Cannot delete complaints (governance rule)

### For Admins
- 🎛️ Complete control room dashboard
- 📊 Real-time metrics and analytics
- 👥 Assign complaints to officers
- 📝 Add official replies
- 📤 Export complaints to CSV (RTI/audit ready)
- 👮 Manage officer roles

## 🔧 Requirements

- **WordPress:** 5.8 or higher
- **PHP:** 7.4 or higher
- **MySQL:** 5.7 or higher
- **Ultimate Member:** 2.x (for profile integration)

## 📥 Installation

### Method 1: Manual Installation

1. **Download** the plugin folder `e-governance-complaint-portal`

2. **Upload** to your WordPress installation:
   ```
   wp-content/plugins/e-governance-complaint-portal/
   ```

3. **Activate** the plugin:
   - Go to WordPress Admin → Plugins
   - Find "e-Governance Complaint Portal"
   - Click "Activate"

### Method 2: Git Clone (Windows)

```powershell
cd path\to\wordpress\wp-content\plugins
git clone https://github.com/yourusername/e-governance-complaint-portal.git
```

Then activate from WordPress admin.

## ⚙️ Configuration

### 1. Initial Setup

After activation, the plugin automatically:
- Creates 3 custom database tables
- Adds custom user roles (health_officer, water_officer, electricity_officer)
- Sets default SLA periods
- Creates upload directory

### 2. Plugin Settings

Navigate to: **WordPress Admin → Complaints → Settings**

Configure:
- **General Settings**
  - Enable/disable complaint submissions
  - Allowed departments
  - State/district lists

- **SLA Settings**
  - Health Department: 7 days (default)
  - Water Department: 15 days (default)
  - Electricity Department: 10 days (default)

- **Email Notifications**
  - Notify citizens on status change
  - Notify officers on assignment

- **Image Upload**
  - Max images per complaint: 5
  - Max file size: 2MB
  - Allowed formats: JPG, PNG, WebP

### 3. Assign Officer Roles

Navigate to: **WordPress Admin → Complaints → Officers**

1. Select a WordPress user
2. Assign department role(s):
   - Health Officer
   - Water Officer
   - Electricity Officer
3. Users can have multiple department roles

## 📖 Usage Guide

### For Citizens

#### Filing a Complaint

1. **Navigate** to your account (Ultimate Member profile)
2. **Click** "File New Complaint" tab
3. **Fill out** the form:
   - Title (10-200 characters)
   - Description (minimum 50 characters)
   - Select department (Health/Water/Electricity)
   - Location: State, District, Ward, Pincode
   - Upload images (optional, max 5)
4. **Submit** and note your Grievance ID

#### Tracking Complaints

- **My Complaints Tab:** View all your submitted complaints
- **Complaint Tracking Tab:** Search by Grievance ID
- **Officer Replies Tab:** See all responses

#### Understanding Status

| Status | Meaning |
|--------|---------|
| **Pending Review** | Awaiting admin review |
| **Approved** | Assigned to department officer |
| **In Progress** | Officer is working on it |
| **Resolved** | Complaint resolved |
| **Rejected** | Complaint rejected (see admin reply) |
| **Escalated** | Sent to higher authority |

### For Officers

#### Accessing Dashboard

Navigate to: **WordPress Admin → My Department**

You'll see:
- Department-specific statistics
- Assigned complaints
- SLA compliance metrics

#### Managing Complaints

1. **View complaint details**
2. **Update status** to:
   - In Progress
   - Resolved
   - Escalated
3. **Add officer reply** (internal notes)

**Note:** Officers cannot delete complaints.

### For Admins

#### Control Room

Navigate to: **WordPress Admin → Complaints → Control Room**

View:
- Total complaints
- Pending review count
- In progress count
- Resolved count
- Overdue (SLA breached)
- Department-wise breakdown
- SLA compliance per department

#### Managing Complaints

Navigate to: **WordPress Admin → Complaints → All Complaints**

Actions available:
- Assign to officer
- Update status (any status)
- Add official reply
- Delete complaint (admins only)

#### Exporting Data (CSV)

Navigate to: **Control Room → Export Section**

Filter by:
- Department
- Status
- Date range

Exported fields:
- Grievance ID
- Title
- Department
- Status
- Date Submitted
- Citizen Name & Email
- Complaint Description
- Location details
- SLA Status
- Resolved Date

## 🔐 Security Features

- ✅ Nonce verification on all forms
- ✅ Capability checks (role-based access)
- ✅ Input sanitization
- ✅ Output escaping
- ✅ Prepared SQL statements
- ✅ File upload validation

## 📊 Database Schema

The plugin creates 3 custom tables:

### 1. egcp_complaints
Stores all complaint data including:
- Grievance ID, title, description
- Department, status, priority
- Location details
- SLA deadline and status
- Assigned officer
- Timestamps

### 2. egcp_replies
Stores officer and admin replies:
- Complaint ID reference
- User ID (who replied)
- Reply type (officer/admin)
- Message content
- Timestamp

### 3. egcp_status_history
Audit trail of status changes:
- Complaint ID reference
- User ID (who changed)
- Old status → New status
- Remarks
- Timestamp

## 🎨 Shortcodes

Use these shortcodes on any page/post:

```
[egcp_complaint_form]
```
Displays complaint submission form

```
[egcp_my_complaints]
```
Shows logged-in user's complaints

```
[egcp_track_complaint]
```
Allows tracking by Grievance ID

## 🔄 SLA (Service Level Agreement)

### How it Works

1. **Complaint submitted** → SLA clock starts
2. **Deadline calculated** based on department:
   - Health: +7 days
   - Water: +15 days
   - Electricity: +10 days
3. **Daily cron job** checks for overdue complaints
4. **Status displayed** in all dashboards

### SLA Statuses

- **Within SLA:** Green badge, days remaining
- **Urgent:** Yellow badge, hours remaining
- **Overdue:** Red badge, days overdue
- **Resolved:** Blue badge (stopped)

## 🧩 Ultimate Member Integration

The plugin adds custom tabs to UM profiles:

1. **File New Complaint** → Complaint form
2. **My Complaints** → List of user's complaints
3. **Complaint Tracking** → Search by Grievance ID
4. **Officer Replies** → All responses
5. **Helpdesk** → FAQs and instructions

These tabs appear only on the user's own profile.

## 🛠️ Development

### File Structure

```
e-governance-complaint-portal/
├── e-governance-complaint-portal.php  # Main plugin file
├── README.md
├── PLAN.md
├── assets/
│   ├── css/                           # Stylesheets
│   └── js/                            # JavaScript files
├── includes/                          # Core classes
├── admin/                             # Admin dashboard
│   ├── class-*.php                    # Admin classes
│   └── views/                         # Admin view templates
└── public/                            # Public-facing
    ├── class-*.php                    # Public classes
    └── views/                         # Public view templates
```

### Custom User Roles

- `health_officer`
- `water_officer`
- `electricity_officer`

Capabilities:
- `read_complaints`
- `update_complaint_status`
- `reply_to_complaint`
- `view_sla_timer`

## 🐛 Troubleshooting

### Issue: Complaints not submitting

**Solution:**
1. Check if user is logged in
2. Verify all required fields are filled
3. Check browser console for JavaScript errors
4. Ensure complaint submissions are enabled in settings

### Issue: Images not uploading

**Solution:**
1. Check file format (JPG, PNG, WebP only)
2. Verify file size (max 2MB)
3. Check PHP upload limits in `php.ini`

### Issue: SLA timer not updating

**Solution:**
1. Ensure WordPress cron is running
2. Check: WP Admin → Tools → Site Health → Cron
3. Manually trigger: `wp cron event run egcp_check_sla`

## 📝 Changelog

### Version 1.0.0 (2026-02-06)
- Initial release
- Complete complaint management system
- Role-based access control
- SLA tracking
- Ultimate Member integration
- CSV export functionality

## 🤝 Support

For support, please contact:
- **Email:** [your-email@example.com]
- **GitHub Issues:** [repository-url]/issues

## 📄 License

GPL v2 or later

## 👨‍💻 Author

**Your Name**
- Website: [yourwebsite.com]
- GitHub: [@yourusername]

---

**Made with ❤️ for e-Governance**