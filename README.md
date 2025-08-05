# UNZANASA Student Union Voting System

A comprehensive web-based voting system built for the UNZANASA Student Union elections. This system provides secure, transparent, and efficient electronic voting capabilities.

## 🚀 Features

### Core Functionality
- **Multi-Election Support**: Handle multiple concurrent elections
- **Sequential Voting**: Users can vote across multiple elections in one session
- **Real-time Results**: Live vote counting and results display
- **Admin Management**: Complete administrative control panel
- **Candidate Management**: Full CRUD operations for candidates
- **Position Management**: Flexible position/office management
- **Voter Authentication**: Computer number-based voter identification

### Security Features
- **Admin Authentication**: Secure admin login system
- **Duplicate Vote Prevention**: One vote per computer per election
- **Session Management**: Secure session handling
- **Input Validation**: Comprehensive data validation
- **SQL Injection Protection**: Parameterized queries

### User Experience
- **Responsive Design**: Mobile-friendly Bootstrap interface
- **Progress Tracking**: Visual progress indicators during voting
- **Completion Tracking**: Clear indication of completed elections
- **Photo Support**: Candidate photo uploads and display
- **Results Visualization**: Comprehensive results with progress bars

## 📋 System Requirements

- **Web Server**: Apache/Nginx with PHP support
- **PHP**: Version 7.4 or higher
- **Database**: MySQL 5.7+ or MariaDB 10.2+
- **Extensions**: PDO, PDO_MySQL, GD (for image handling)

## 🛠 Installation

### 1. Database Setup
```sql
-- Import the database schema
mysql -u your_username -p your_database < unzanasa_voting.sql
```

### 2. Configuration
Update database credentials in `application/libraries/Database.php`:
```php
private $host = 'localhost';
private $user = 'your_username';
private $pass = 'your_password';
private $dbname = 'your_database';
```

### 3. Initial Setup
```bash
# Create initial admin account
php setup-admin.php

# Optional: Add test data
php setup-test-data.php
```

### 4. File Permissions
```bash
chmod 755 uploads/
chmod 755 uploads/candidates/
```

## 🎯 Usage

### Admin Access
1. Navigate to `admin-login.php`
2. Default credentials: `admin` / `admin123`
3. Access admin dashboard for system management

### Voting Process
1. Users visit `vote.php`
2. Enter computer number for identification
3. Vote sequentially across all active elections
4. Receive confirmation upon completion

### Results Viewing
- Public results available at `view-results.php`
- Real-time vote counts and percentages
- Winner highlighting and statistics

## 📁 Project Structure

```
unzanasa-voting-system/
├── application/
│   ├── controllers/     # MVC controllers
│   ├── models/         # Data models (Admin, Election, Candidate, Vote, Position)
│   ├── libraries/      # Core libraries (Database)
│   └── views/          # View templates
├── uploads/
│   └── candidates/     # Candidate photo storage
├── admin-login.php     # Admin authentication
├── admin-dashboard.php # Admin control panel
├── manage-elections.php # Election management
├── manage-candidates.php # Candidate management
├── manage-admins.php   # Admin user management
├── vote.php           # Main voting interface
├── vote-complete.php  # Vote completion page
├── view-results.php   # Results display
├── init.php          # System initialization
├── index.php         # Main entry point
└── README.md         # This file
```

## 🔧 Key Components

### Models
- **Admin**: User authentication and management
- **Election**: Election lifecycle management
- **Candidate**: Candidate information and status
- **Vote**: Vote casting and validation
- **Position**: Electoral positions/offices

### Core Features
- **Database Class**: PDO-based database abstraction
- **Session Management**: Secure admin sessions
- **File Upload**: Candidate photo handling
- **Vote Validation**: Duplicate prevention
- **Results Calculation**: Real-time vote counting

## 🎨 User Interface

### Admin Interface
- Clean, professional Bootstrap-based design
- Intuitive navigation between management sections
- Real-time feedback and status messages
- Responsive tables and forms

### Voting Interface
- User-friendly voting experience
- Clear candidate information display
- Progress tracking across elections
- Mobile-optimized layout

### Results Interface
- Comprehensive results display
- Visual progress bars and statistics
- Winner highlighting
- Election-specific and overview modes

## 🔒 Security Measures

1. **Authentication**: Secure admin login with password hashing
2. **Authorization**: Session-based access control
3. **Input Validation**: Server-side validation for all inputs
4. **SQL Injection Prevention**: Parameterized queries
5. **File Upload Security**: Type and size validation
6. **Vote Integrity**: Computer number-based duplicate prevention

## 🚨 Important Notes

### Default Credentials
- **Username**: `admin`
- **Password**: `admin123`
- **⚠️ Change these credentials immediately after installation**

### Candidate Status
- Candidates are **active by default** when created
- Inactive candidates don't appear in voting forms
- Status can be toggled in candidate management

### Voting Rules
- One vote per computer number per election
- Sequential voting across multiple elections
- Completion tracking prevents premature exit

## 🔄 Maintenance

### Regular Tasks
1. **Database Backup**: Regular backups of voting data
2. **Log Monitoring**: Check error logs for issues
3. **File Cleanup**: Periodic cleanup of uploaded files
4. **Security Updates**: Keep PHP and database updated

### Troubleshooting
1. **Database Connection**: Check credentials in Database.php
2. **File Permissions**: Ensure uploads directory is writable
3. **Admin Access**: Use setup-admin.php to reset admin credentials
4. **Vote Issues**: Check votes table constraints and foreign keys

## 📊 Database Schema

### Key Tables
- `admins`: Administrator accounts
- `elections`: Election definitions
- `positions`: Electoral positions/offices
- `candidates`: Candidate information
- `votes`: Cast votes
- `voters`: Voter registration data

### Relationships
- Elections → Positions (1:many)
- Positions → Candidates (1:many)
- Elections + Voters → Votes (many:many)
- Candidates → Votes (1:many)

## 🤝 Support

For technical support or questions:
1. Check the troubleshooting section
2. Review error logs in your web server
3. Verify database connectivity and permissions
4. Ensure all required PHP extensions are installed

## 📝 License

This project is developed for the UNZANASA Student Union. All rights reserved.

---

**Version**: 2.0  
**Last Updated**: January 2025  
**Status**: Production Ready ✅

### Recent Fixes Applied
- ✅ Admin dashboard statistics display
- ✅ Election management action buttons
- ✅ Vote casting foreign key constraints
- ✅ Candidate management functionality
- ✅ Sequential voting across elections
- ✅ Comprehensive results viewing
- ✅ Admin registration system
- ✅ Candidate active status by default
- ✅ Database type compatibility (arrays vs objects)
- ✅ Complete system cleanup and optimization

The system is now fully functional and ready for production use! 🎉
