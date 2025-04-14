# 🚀 QuickHire Labor Application Setup Guide

## 📋 Prerequisites
- 🔧 XAMPP with PHP 7.4+ and MySQL 5.7+
- 🌐 Web browser
- 📦 Git (optional)

## ⚙️ Installation Steps

1. **💾 Setup Database**
   - 🟢 Start XAMPP Control Panel
   - 🔄 Start Apache and MySQL services
   - 🗄️ Open phpMyAdmin (http://localhost/phpmyadmin)
   - 📝 Create a new database named `lastop`

2. **🛠️ Configure Application**
   - 📂 Copy all project files to `c:\xampp\htdocs\QuickHireLabor\`
   - ⚡ Verify database configuration in `config.php`:
     ```php
     define('DB_HOST', 'localhost');
     define('DB_USER', 'root');
     define('DB_PASS', '');
     define('DB_NAME', 'lastop');
     ```

3. **🔨 Run Setup Scripts**
   - 🔗 Access setup.php: http://localhost/QuickHireLabor/setup.php
     - 📁 Creates necessary directories and permissions
     - 🖼️ Sets up upload folders for images
   - 🔧 Access sql_setup.php: http://localhost/QuickHireLabor/sql_setup.php
     - 📊 Creates database tables
     - 🔄 Inserts initial data:
       - 👨‍💼 Admin user
       - 👥 Sample users
       - 🛠️ Service categories
       - 📋 Sample jobs

4. **🔑 Default User Accounts**

   **👨‍💼 Admin Account**
   - 📧 Email: admin@lastop.com
   - 🔒 Password: admin123

   **👷 Labor Account**
   - 📧 Email: labor@gmail.com
   - 🔒 Password: labor123

   **👤 Customer Account**
   - 📧 Email: customer@gmail.com
   - 🔒 Password: customer123

5. **📁 File Structure**
```
QuickHireLabor/
├── 📝 config.php      # Core configuration
├── 🔧 setup.php       # Setup script
├── 📊 sql_setup.php   # Database script
├── 📁 uploads/        # Uploaded content
│   ├── 🖼️ profile_pics/
│   └── 📷 job_images/
├── 📂 includes/       # Components
├── 🎨 css/           # Styles
└── 🖼️ images/        # Static images
```

6. **🧪 Testing the Installation**
   - 🌐 Visit http://localhost/QuickHireLabor/
   - 🔑 Test admin login
   - 👥 Create test accounts
   - ✅ Test core features

## ❗ Troubleshooting

1. **🔌 Database Connection Issues**
   - ✔️ Check XAMPP services
   - 🔍 Verify database credentials
   - ✅ Confirm database exists

2. **📂 Upload Permissions**
   - 🔒 Fix folder permissions:
     ```bash
     chmod 777 uploads/profile_pics
     chmod 777 uploads/job_images
     ```

3. **⚠️ Common Errors**
   - 📋 "Table not found" → Re-run sql_setup.php
   - 📁 "Cannot write file" → Check permissions
   - 🔌 "Connection failed" → Check config

## 🔒 Security Notes

1. 🔑 Update admin password immediately
2. 🛡️ Set proper permissions
3. 🔐 Secure database credentials
4. ⚠️ Manage error reporting

## 💬 Support

Need help? Check:
- 📝 XAMPP error logs
- ⚠️ PHP error log
- 📧 Contact admin support


# Database Migrations

This directory is used for CSV files containing data to be imported into the database tables.

## How to Use

1. Create a CSV file with the exact name of the table you want to import data into (e.g., `services.csv` for the `services` table)
2. The first row of the CSV must contain the column names exactly as they appear in the database
3. Subsequent rows should contain the data to be imported
4. Place the CSV file in this directory
5. Run the `sql_setup.php` script to import the data

## Example

For the `services` table, create a file named `services.csv` with contents like:

