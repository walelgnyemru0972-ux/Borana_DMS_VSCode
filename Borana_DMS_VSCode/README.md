# Borana University Dormitory Management System (VS Code)

This is a cleaned, runnable PHP + MySQL implementation based on the uploaded `DMS.zip`. The archive contained legacy PHP pages and scripts, but did not include a database export or all referenced assets/pages. This version supplies a consistent database schema and working core workflows instead of depending on missing files.

## Features
- Login with role-based access: administrator, proctor manager, proctor, and student.
- Register blocks with a male/female category and faculty.
- Register rooms with a maximum configured capacity of six.
- Add students and assign them to rooms.
- Give each student a login and a private page showing their own dorm assignment.
- Enforce that a student's sex matches the block category.
- Prevent assigning students to full rooms.
- Dashboard totals and student/room lists.
- Prepared SQL statements, hashed passwords, CSRF checks, and escaped output.

## Requirements
- Windows 10/11
- VS Code
- XAMPP (Apache + MySQL/MariaDB + PHP)
- A modern browser

## A. Install XAMPP
1. Download XAMPP from https://www.apachefriends.org/
2. Install it (the default folder is `C:\xampp`).
3. Open the XAMPP Control Panel.
4. Click **Start** beside **Apache** and **MySQL**.

## B. Open the project in VS Code
1. Extract the downloaded `Borana_DMS_VSCode.zip`.
2. Copy the extracted `Borana_DMS_VSCode` folder into `C:\xampp\htdocs\`.
   Your main path should be `C:\xampp\htdocs\Borana_DMS_VSCode\index.php`.
3. Open VS Code.
4. Select **File → Open Folder** and choose `C:\xampp\htdocs\Borana_DMS_VSCode`.

## C. Create the database
1. Open `http://localhost/phpmyadmin` in your browser.
2. Select **Import**.
3. Choose the project's `database.sql`.
4. Click **Import** or **Go**.
5. The SQL script creates the `borana_dms` database and its tables.

If your MySQL root account has a password, edit `DB_PASS` in `config.php`. The default XAMPP root password is commonly blank on a local installation. This project's current `DB_HOST` is `127.0.0.1:3307`; change it to `127.0.0.1` if your MySQL server uses the default port 3306, or set it to the port shown for MySQL in XAMPP.

If you already imported an older version of `database.sql`, run `database_upgrade_student_login.sql` once in phpMyAdmin (select the `borana_dms` database, open **Import**, choose the upgrade file, and click **Go**). This adds the missing student password column without removing student records. Do not run it again after the column has been added. For a new installation, just import `database.sql` instead.

If you already have the database and want to allow room capacities of five or six, run `database_upgrade_room_capacity.sql` once in phpMyAdmin (select the `borana_dms` database, open **Import**, choose the upgrade file, and click **Go**). New installations already allow capacities from one through six.

## D. Create the first administrator
1. Visit `http://localhost/Borana_DMS_VSCode/setup_admin.php` once.
2. It creates the first administrator account.
3. Login at `http://localhost/Borana_DMS_VSCode/login.php`:
   - Username: `admin`
   - Password: `Admin@12345`
4. **Immediately delete `setup_admin.php`** after the account is created. Do not leave the setup script on a public server.
5. This starter version does not provide self-service password change yet. For a real deployment, add password-change and account recovery pages before production use.

## E. Use the system
1. Login as administrator.
2. Open **Blocks** and register blocks. Select Male or Female for each block.
3. Open **Rooms** and add rooms to the appropriate blocks. Capacity may be 1–6 students.
4. Open **Students** and add each student's ID, name, sex, batch, department, and a login password (at least 8 characters). Give the student their student ID and password; the student ID is their login name.
5. In **Assign a room**, select an unassigned student and an available room.
6. The system rejects assignments if the student's sex does not match the block category or the room is full.
7. As administrator, open **User accounts** using `http://localhost/Borana_DMS_VSCode/users.php` to create proctor manager and proctor accounts.
8. Students imported from the updated university list sign in at `http://localhost/Borana_DMS_VSCode/login.php` using their first name as the username and their student ID as the initial password. Other student accounts can sign in with their student ID and the password set by staff. Students are taken to **My dorm**, which shows only their own details, block, and room. Staff can set or reset passwords from the student's row on the **Students** page.

## F. Run/debug using VS Code terminal (optional)
If PHP is installed and available in your PATH, open VS Code Terminal in the project directory and run:
```bash
php -S localhost:8000
```
Then visit `http://localhost:8000`. This does not start MySQL; XAMPP MySQL must still be running and `config.php` must point to the right database.

For the simplest Windows setup, use Apache via XAMPP and visit `http://localhost/Borana_DMS_VSCode/`.

## Troubleshooting
- **404 Not Found:** Make sure the project folder is inside `C:\xampp\htdocs\` and the URL folder name matches exactly.
- **Database connection failed:** Start MySQL in XAMPP, import `database.sql`, and check DB settings in `config.php`.
- **Access denied for user root:** Set `DB_PASS` to your local MySQL password.
- **Login fails:** Run `setup_admin.php` once after importing the database. If the admin user already exists, the script intentionally refuses to recreate it.
- **Room assignment fails:** Verify that the student's sex matches the block's sex category and the room has remaining capacity.
- **Blank page/PHP error:** Check `C:\xampp\php\logs\php_error_log` or Apache logs in the XAMPP Control Panel.

## Important
This project is for local coursework/demo use. Before real university deployment, add formal audit logging, password-change/recovery, stricter operational roles, database backups, deployment HTTPS, and institutional approval. Do not use real student personal data on an unsecured public server.
