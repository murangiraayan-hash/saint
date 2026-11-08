# Saint Michael Technical High School - PHP Backend Setup Guide

This guide walks you through hosting the PHP backend on your computer using **XAMPP** (or WAMP / MAMP / LAMP).

---

## 1. Download & Install XAMPP
1. Download XAMPP from [apachefriends.org](https://www.apachefriends.org/).
2. Run the installer and install **Apache** and **MySQL**.
3. Open the **XAMPP Control Panel** and click **Start** next to Apache and MySQL.

---

## 2. Place Backend Files in htdocs
1. Navigate to your XAMPP web directory:
   - On Windows: `C:\xampp\htdocs\`
   - On Mac: `/Applications/XAMPP/htdocs/`
   - On Linux: `/opt/lampp/htdocs/`
2. Create a new folder named `smths_backend`:
   ```
   C:\xampp\htdocs\smths_backend\
   ```
3. Put the following PHP files into that folder:
   - `config.php`
   - `register.php`
   - `pay.php`
   - `get_students.php`
   - `admin.php`

---

## 3. Create the Database in phpMyAdmin
1. Open your web browser and go to:
   `http://localhost/phpmyadmin`
2. Click on the **SQL** tab at the top.
3. Open the `database.sql` file, copy all its content, and paste it into the SQL box.
4. Click **Go** (bottom right).
5. It will create the database `smths_school_db` with tables `programs`, `registrations`, and `payments`.

---

## 4. Test Your PHP Backend
1. **Test Registration Form in browser or Postman:**
   Send a `POST` request to:
   ```
   http://localhost/smths_backend/register.php
   ```
   Sample JSON body:
   ```json
   {
     "name": "Kwizera Jean",
     "dob": "2007-06-15",
     "district": "Gasabo, Kigali",
     "school": "Kigali Secondary School",
     "prog": "p1",
     "parent": "Mutesi Alice",
     "phone": "0788112233",
     "email": "jean@example.com",
     "msg": "Interested in software development"
   }
   ```

2. **Open the Admin Portal:**
   Visit in your browser:
   ```
   http://localhost/smths_backend/admin.php
   ```
   You will see all registered students and an "Export to CSV" button.

---

## 5. Connecting with an HTML Form
If you want to use a pure HTML form without JavaScript, write:
```html
<form action="http://localhost/smths_backend/register.php" method="POST">
  <input type="text" name="name" placeholder="Student Full Name" required>
  <input type="date" name="dob" required>
  <input type="text" name="district" placeholder="District" required>
  <select name="prog" required>
    <option value="p1">Software Development</option>
    <option value="p2">Food and Beverage</option>
    <option value="p3">Computer Systems</option>
    <option value="p4">Architecture</option>
    <option value="p5">Multimedia</option>
  </select>
  <input type="text" name="parent" placeholder="Parent Name" required>
  <input type="tel" name="phone" placeholder="Parent Phone" required>
  <button type="submit">Submit to PHP Database</button>
</form>
```

---

## 6. Common Issues & Solutions
- **Error: "Access denied for user 'root'@'localhost'"**:
  Check `config.php`. If your MySQL has a password, change `define('DB_PASS', 'your_password');`.
- **Error: CORS blocked in browser**:
  Ensure the header `header('Access-Control-Allow-Origin: *');` is present in `config.php`.
- **PHP extension PDO not loaded**:
  In `php.ini`, ensure the line `extension=pdo_mysql` does not have a semicolon at the beginning.
