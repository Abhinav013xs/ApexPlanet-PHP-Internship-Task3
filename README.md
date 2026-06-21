# Task 4: Security Enhancements & Role-Based Access Control

Welcome to **Task 4** of the Web Development Internship. This task upgrades the Blog Management System by introducing core security middleware, role-based access rules (Administrators vs. Editors), XSS protections, SQL injection guards via prepared statements, and client/server validation parameters.

---

## 1. Project Folder Structure

Ensure your files are placed exactly as shown below:

```text
blog-project/
├── index.php             # Public homepage listing blog posts (XSS & SQLi guarded)
├── register.php          # User registration view with server/client validations
├── login.php             # User login view with session hardening (fixation guarded)
├── logout.php            # Session termination logic (clears cookies)
├── dashboard.php         # Author control panel enforcing RBAC access rules
├── create-post.php       # Form to add a post associated with user session
├── edit-post.php         # Form to modify owned posts (or any post if Admin)
├── delete-post.php       # Controller to remove owned posts (or any post if Admin)
├── search.php            # Dedicated public search results page (XSS guarded)
├── admin/
│   ├── users.php         # User management directory (restricted to Admin)
│   └── roles.php         # User roles modifier panel (restricted to Admin)
├── middleware/
│   ├── auth.php          # Authentication middleware
│   └── admin.php         # Administration permission guard middleware
├── config/
│   └── database.php      # PDO database connection handler (emulation disabled)
├── css/
│   └── style.css         # Styling rules overrides
├── js/
│   └── script.js         # JavaScript confirmation dialogs
└── README.md             # Project instruction guide (this file)
```

---

## 2. Security Enhancements Implemented

### A. SQL Injection Prevention (Prepared Statements)
All database interactions are refitted with secure PDO Prepared Statements. Connection settings explicitly disable query emulation to force native prepared checks:
```php
$conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
```
No variables are concatenated directly into queries. All parameters are executed using bound variables (e.g. `:username`, `:id`).

### B. Cross-Site Scripting (XSS) Protection
All user-generated variables (usernames, post titles, post contents) are escaped before rendering inside HTML templates using:
```php
htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
```

### C. Hardened Session Security
1. **Secure Session Settings**: Session cookies are configured with strict protection flags:
   * `HttpOnly`: Prevents client-side scripts from reading session cookies.
   * `SameSite=Strict`: Shields session cookies from Cross-Site Request Forgery (CSRF).
   * `Secure`: Forces cookies to transmit only over HTTPS connections.
2. **Session Fixation Guard**: Invokes `session_regenerate_id(true)` upon successful user authentication.
3. **Session Destruction**: Clear session variables, invalidate cookies, and call `session_destroy()` on logout.

### D. Server & Client Validation
1. **Client-Side**: Event listeners intercept form submissions to verify username lengths (>=3 chars), password strengths (>=6 chars), and blank inputs, generating user-friendly validation messages.
2. **Server-Side**: Re-validates data integrity using strict PHP filters before executing SQL updates.

### E. Role-Based Access Control (RBAC)
*   **Administrator**: Can create, edit, delete any post, browse the user registry, and toggle user roles.
*   **Editor**: Can create posts, edit/delete *their own* posts, and cannot access the user registry panel.

---

## 3. Database Schema Updates (`schema.sql`)

Run the following SQL statements to apply Task 4 updates to your MySQL database:

```sql
USE `blog`;

-- 1. Add role column to users
ALTER TABLE `users` ADD COLUMN `role` ENUM('admin', 'editor') DEFAULT 'editor';

-- 2. Add user_id column to posts
ALTER TABLE `posts` ADD COLUMN `user_id` INT DEFAULT NULL;

-- 3. Establish foreign key constraint
ALTER TABLE `posts` ADD CONSTRAINT fk_posts_users FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

-- 4. Seed default admin (username: 'admin', password: 'admin123' hashed)
INSERT INTO `users` (`username`, `password`, `role`) 
VALUES ('admin', '$2y$10$MvNfX7pX7qVlQjFfRk5TdeY0JqL2c0V2YyX.1kY8O7t8wz9o9u9zG', 'admin')
ON DUPLICATE KEY UPDATE `role` = 'admin';
```

---

## 4. Run Instructions

1. Start **Apache** and **MySQL** in your **XAMPP Control Panel**.
2. Open your browser and navigate to:
   * **`http://localhost:8000`**
3. **Verification Steps**:
   - Log in as the default admin using: username **`admin`** and password **`admin123`**. Note the "Manage Users" option in the navbar.
   - Register a new account. Note that new registrations default to the `editor` role.
   - Log in as the editor. Verify that you can create posts and edit them, but cannot see or modify other users' posts.
   - Try navigating manually to `http://localhost:8000/admin/users.php` while logged in as an editor, and verify the access denied block page.
