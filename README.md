# BTE Result Management System

A comprehensive web-based result and academic management system for the Board of Technical Education, Haryana.

## Features

### For Students
- View examination results
- Check attendance records
- Access academic information
- View announcements and notifications

### For Faculty
- Manage classes and student lists
- Mark student attendance
- Enter internal and external examination marks
- View student performance across programs
- Dynamic filtering by Institution, Department, Year, and Semester

### For Administrators
- Manage institutions and programs
- User approval and management
- System-wide administration

## Technology Stack

- **Backend**: PHP 7.4+
- **Database**: MySQL/MariaDB
- **Frontend**: HTML5, CSS3, JavaScript
- **Server**: Apache (XAMPP)

## Installation Instructions

### Prerequisites
- XAMPP (or similar LAMP/WAMP stack)
- PHP 7.4 or higher
- MySQL/MariaDB 5.7 or higher

### Step 1: Setup XAMPP
1. Download and install XAMPP from https://www.apachefriends.org/
2. Start Apache and MySQL services from XAMPP Control Panel

### Step 2: Database Setup
1. Open phpMyAdmin (http://localhost/phpmyadmin)
2. Import the main database:
   - Click on "Import" tab
   - Choose file: `SQL_Database_Backup.sql`
   - Click "Go" to import

3. Run the additional updates:
   - Select the `bte_result_system` database
   - Click on "SQL" tab
   - Copy and paste contents of `database_updates.sql`
   - Click "Go" to execute

### Step 3: Install the Application
1. Copy all project files to your XAMPP htdocs directory:
   ```
   C:\xampp\htdocs\bte_system\
   ```

2. Update database configuration if needed:
   - Open `config/database.php`
   - Verify database credentials (default: root with no password)

### Step 4: Access the Application
1. Open your web browser
2. Navigate to: `http://localhost/bte_system/`

## Project Structure

```
bte_system/
├── config/
│   └── database.php          # Database configuration
├── includes/
│   ├── header.php           # Common header
│   └── footer.php           # Common footer
├── assets/
│   ├── css/
│   │   └── style.css        # Main stylesheet
│   └── images/              # Image assets
├── ajax/
│   ├── get_programs.php     # Fetch programs by institution
│   ├── get_program_years.php # Fetch years for program
│   ├── get_semesters.php    # Fetch semesters by year
│   ├── get_students.php     # Fetch student list
│   ├── get_students_for_subject.php
│   └── get_marks.php        # Fetch existing marks
├── faculty/
│   ├── dashboard.php        # Faculty dashboard
│   ├── marks.php           # Marks entry page
│   └── attendance.php      # Attendance management
├── student/
│   └── dashboard.php        # Student dashboard
├── admin/
│   └── dashboard.php        # Admin dashboard
├── index.php               # Home page
├── login.php               # Login page
├── register.php            # Registration page
├── logout.php              # Logout handler
├── about.php               # About page
└── database_updates.sql    # Additional database updates
```

## User Registration

### As a Student
1. Click "Register Now" from home page
2. Select role: "Student"
3. Fill in personal details:
   - Name, Email, Phone, Date of Birth, Gender
4. Select your Institution and Program
5. Enter Roll Number, Father's Name, Admission Year
6. Provide your address
7. Create a password
8. Submit registration (wait for admin approval)

### As a Faculty Member
1. Click "Register Now" from home page
2. Select role: "Faculty"
3. Fill in personal details:
   - Name, Email, Phone, Date of Birth, Gender
4. Select your Institution and Department/Program
5. Enter Designation, Date of Joining, Pay Level
6. Optional: Add alternate phone and email
7. Create a password
8. Submit registration (wait for admin approval)

## Faculty Dashboard Usage

### Viewing Students
1. Login as faculty
2. From dashboard, scroll to "Select Class to View Students"
3. Select filters:
   - **Institution**: Your assigned institution
   - **Program/Department**: Department you teach
   - **Year**: Academic year (1, 2, 3, etc.)
   - **Semester**: Semester within that year
4. Click "View Students" to see the complete list

### Entering Marks
1. Navigate to "Marks Entry" from dashboard
2. Select:
   - **Subject**: Choose from your assigned subjects
   - **Student**: Select student from dropdown
   - **Exam Session**: Select examination session
3. Enter marks:
   - Internal Theory (Max: 30)
   - Internal Practical (Max: 20)
   - External Theory (Max: 70)
   - External Practical (Max: 80)
4. Click "Save Marks"

### Managing Attendance
1. Click "Mark Attendance" from dashboard
2. Select class, date, and subject
3. Mark attendance for each student
4. Submit attendance record

## Database Tables

### Main Tables
- **users**: User authentication and role management
- **student**: Student information and enrollment
- **faculty**: Faculty member details
- **institution**: Institution/college information
- **program**: Academic programs/courses
- **subject**: Subject/course details
- **result**: Examination marks and results
- **attendance**: Attendance records
- **faculty_subject_assignment**: Faculty-subject mapping
- **institution_program**: Institution-program mapping

## Default Credentials

After installation, you'll need to manually approve users or create an admin account directly in the database.

To create an admin user:
```sql
INSERT INTO users (Email, Password, Role, Status) 
VALUES ('admin@bte.gov.in', '$2y$10$encrypted_password_here', 'admin', 'active');
```

Use the register page to create accounts, which will be in 'pending' status until approved.

## Security Features

- Password hashing using PHP's password_hash()
- SQL injection protection via prepared statements
- Session-based authentication
- Role-based access control
- XSS protection through htmlspecialchars()

## Troubleshooting

### Database Connection Error
- Verify XAMPP MySQL is running
- Check database credentials in `config/database.php`
- Ensure database `bte_result_system` exists

### Page Not Loading
- Ensure Apache is running in XAMPP
- Check that files are in correct htdocs directory
- Verify PHP errors in XAMPP error logs

### Can't Login
- Verify user status is 'active' in database
- Check email and password are correct
- Clear browser cache and cookies

## Future Enhancements

- SMS/Email notifications
- PDF report generation
- Bulk marks upload via Excel
- Advanced analytics and reporting
- Mobile app integration
- Parent portal

## Support

For technical support or queries, contact:
- Email: support@bteharyana.gov.in
- Phone: 0172-2993512

## License

© 2026 Board of Technical Education, Haryana. All rights reserved.
