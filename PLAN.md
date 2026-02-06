# e-Governance Complaint Portal - Development Plan

## Plugin Overview
**Name:** e-Governance Complaint Portal  by shub
**Version:** 1.0.0  
**Purpose:** Government complaint management system with role-based access control

---

## Phase 1: Foundation & Setup ✓

### 1.1 Plugin Structure
- [x] Create folder structure
- [ ] Main plugin file with header
- [ ] Activation/Deactivation hooks
- [ ] Autoloader for classes

### 1.2 Custom User Roles
Create WordPress roles:
- `health_officer` (Health Department)
- `water_officer` (Water Department)
- `electricity_officer` (Electricity Department)

**Capabilities:**
- `read_complaints` - View department complaints
- `update_complaint_status` - Change status
- `reply_to_complaint` - Add officer replies
- Officers CANNOT delete complaints

### 1.3 Database Schema
**Table: `{prefix}_egcp_complaints`**
```sql
- id (bigint, primary key)
- grievance_id (varchar 20, unique)
- citizen_id (bigint, user ID)
- title (varchar 200)
- description (text)
- department (varchar 50) - health|water|electricity
- status (varchar 20) - pending|approved|in_progress|resolved|rejected|escalated
- priority (varchar 20) - low|medium|high
- state (varchar 100)
- district (varchar 100)
- ward (varchar 100)
- pincode (varchar 10)
- images (text, JSON array of URLs)
- sla_deadline (datetime)
- sla_status (varchar 20) - within_sla|overdue
- assigned_officer (bigint, user ID)
- created_at (datetime)
- updated_at (datetime)
```

**Table: `{prefix}_egcp_replies`**
```sql
- id (bigint, primary key)
- complaint_id (bigint, foreign key)
- user_id (bigint)
- reply_type (varchar 20) - officer|admin
- message (text)
- created_at (datetime)
```

**Table: `{prefix}_egcp_status_history`**
```sql
- id (bigint, primary key)
- complaint_id (bigint)
- changed_by (bigint, user ID)
- old_status (varchar 20)
- new_status (varchar 20)
- remarks (text)
- created_at (datetime)
```

---

## Phase 2: Core Functionality

### 2.1 Complaint Submission (Citizen)
- Frontend form with fields:
  - Title (required)
  - Description (required)
  - Department dropdown (Health/Water/Electricity)
  - State dropdown
  - District dropdown
  - Ward/Area text field
  - Pincode
  - Image upload (max 5, optional)
- Generate unique Grievance ID: `EGCP-DEPT-YYYYMMDD-XXXX`
- AJAX form submission
- Success message with Grievance ID

### 2.2 SLA Management
**SLA Duration (configurable per department):**
- Health: 7 days (default)
- Water: 15 days (default)
- Electricity: 10 days (default)

**SLA Calculation:**
- Start from `created_at` timestamp
- Calculate deadline: `created_at + SLA days`
- Auto-update `sla_status` to "overdue" when deadline passes
- Display SLA timer in dashboards

### 2.3 Status Workflow
```
Citizen Submits → PENDING
    ↓
Admin Reviews → APPROVED (assigns to officer)
    ↓
Officer Actions:
    - IN_PROGRESS (working on it)
    - RESOLVED (completed)
    - ESCALATED (needs higher authority)
    ↓
Admin Actions:
    - REJECTED (invalid complaint)
    - ESCALATED (to higher department)
```

---

## Phase 3: Citizen Dashboard (Ultimate Member Tab)

### 3.1 UM Integration
- Create custom UM tab: "My Complaints"
- Sub-tabs:
  1. **File New Complaint** (form)
  2. **My Complaints** (list view)
  3. **Complaint Tracking** (detailed view)
  4. **Officer Replies** (conversations)
  5. **Helpdesk** (FAQs/instructions)

### 3.2 Display Information
Each complaint shows:
- Grievance ID
- Title
- Status badge (color-coded)
- Department
- Location (State, District)
- Submitted Date
- SLA Status (Days Remaining / Overdue)
- Admin Reply (if any)
- Officer Reply (if any)

---

## Phase 4: Officer Dashboard

### 4.1 Department-Specific View
- Officer sees only their department complaints
- Filter by status
- SLA timer visible
- Bulk actions: Update status, Add reply

### 4.2 Officer Actions
- **View Details** (read-only for citizen info)
- **Update Status** (dropdown: In Progress, Resolved, Escalated)
- **Add Officer Reply** (internal/technical notes)
- **Cannot Delete** (governance rule)

### 4.3 Officer Dashboard Widgets
- Total Assigned Complaints
- Pending Action
- Overdue (SLA breached)
- Resolved This Month

---

## Phase 5: Admin Control Room

### 5.1 Grievance Control Room
**Metrics Dashboard:**
- Total Complaints (all time)
- Pending Review
- In Progress
- Resolved
- Overdue (SLA breached)
- Rejected

**Charts/Graphs:**
- Complaints by Department (pie chart)
- Monthly Trends (line chart)
- SLA Performance (bar chart)

### 5.2 Admin Capabilities
- **View All Complaints** (all departments)
- **Assign Department** (if needed)
- **Assign Officer** (to specific user)
- **Add Admin Reply** (official/public response)
- **Update Status** (any status)
- **Export CSV** (RTI/Audit ready)
- **Delete Complaint** (only super admin)

### 5.3 CSV Export Columns
- Grievance ID
- Title
- Department
- Status
- Date Submitted
- Citizen Name
- Citizen Email
- Complaint Description
- Location (State, District, Ward, Pincode)
- Admin Reply
- Officer Reply
- SLA Status
- Resolved Date

---

## Phase 6: Settings & Configuration

### 6.1 Plugin Settings Page
**General Settings:**
- Enable/Disable complaint submission
- Allowed departments (Health, Water, Electricity, Custom)
- State/District list (JSON or manual input)

**SLA Settings:**
- Health Department SLA (days)
- Water Department SLA (days)
- Electricity Department SLA (days)

**Email Notifications:**
- Notify citizen on status change (Yes/No)
- Notify officer on new assignment (Yes/No)
- Admin email for escalations

**Image Upload Settings:**
- Max images per complaint (default: 5)
- Max file size (default: 2MB)
- Allowed formats (jpg, png, jpeg, webp)

---

## Phase 7: Security & Validation

### 7.1 Security Checklist
- [x] Nonces for all forms
- [x] Capability checks (current_user_can)
- [x] Sanitize all inputs
- [x] Escape all outputs
- [x] Prepared statements for DB queries
- [x] File upload validation (MIME type, size)

### 7.2 Validation Rules
- Title: 10-200 characters
- Description: 50-2000 characters
- Email: Valid format
- Pincode: 6 digits
- Images: Max 5, each < 2MB

---

## Phase 8: Testing & Deployment

### 8.1 Test Cases
- [ ] Citizen can submit complaint
- [ ] Admin can assign officer
- [ ] Officer can update status
- [ ] SLA timer works correctly
- [ ] CSV export includes all fields
- [ ] UM tab displays correctly
- [ ] Role capabilities enforced

### 8.2 Deployment Checklist
- [ ] Version control (Git)
- [ ] README.md with installation instructions
- [ ] Changelog
- [ ] Screenshots
- [ ] Documentation

---

## File Development Order

1. **Database & Activation**
   - `class-egcp-database.php`
   - `class-egcp-activator.php`
   - `class-egcp-deactivator.php`

2. **User Roles**
   - `class-egcp-roles.php`

3. **Core Classes**
   - `class-egcp-complaint.php` (CRUD operations)
   - `class-egcp-sla.php` (SLA calculations)

4. **Frontend**
   - `class-egcp-shortcodes.php`
   - `public/views/complaint-form.php`
   - `class-egcp-citizen.php`

5. **Admin**
   - `class-egcp-admin-menu.php`
   - `class-egcp-control-room.php`
   - `admin/views/control-room.php`

6. **Officer**
   - `class-egcp-officer-dashboard.php`
   - `admin/views/officer-dashboard.php`

7. **Ultimate Member**
   - `class-egcp-um-integration.php`

8. **Assets**
   - CSS files
   - JavaScript files

---

## Technical Stack

- **WordPress:** 5.8+
- **PHP:** 7.4+
- **MySQL:** 5.7+
- **JavaScript:** Vanilla JS (no jQuery dependency)
- **CSS:** Custom (no frameworks)
- **Integration:** Ultimate Member 2.x

---

## WordPress APIs Used

- Custom User Roles & Capabilities
- Custom Database Tables (wpdb)
- Settings API
- Shortcode API
- AJAX API (`wp_ajax_*`)
- Nonce System
- File Upload (wp_handle_upload)
- Transients (for caching)
- Cron Jobs (SLA checks)

---

## Next Steps

After approval, we'll start coding in this order:
1. Main plugin file
2. Database schema
3. User roles
4. Complaint submission form
5. Admin control room
6. Officer dashboard
7. UM integration
8. Settings page
9. Testing

**Estimated Development Time:** 15-20 steps

---

*Last Updated: 2026-02-06*