# The Arcane Archive — Library Management System

A beginner-friendly PHP + MySQL Library Management System with a Hogwarts-inspired magical archive interface.


### Separate authentication entrances
- `user_login.php` — student/user login only.
- `admin_login.php` — administrator login only.
- `login.php` — entrance hall where the user chooses the correct portal.
- `register.php` — creates **user accounts only**.
- Admin accounts are created separately with `setup_admin.php`.

### User permissions
A normal user can:
- Login through the Student Entrance.
- Browse/search books.
- Borrow up to 2 books.
- Keep each book for 7 days.
- Return books.
- View borrowing history.
- Change their password.

A normal user **cannot**:
- Add books.
- Edit books.
- Delete books.
- Access the admin dashboard.
- View other users' borrowing records.

### Admin permissions
An administrator can:
- Login through the Administrator Entrance.
- See total books, available books, users and active loans.
- Add books.
- Edit books.
- Remove available books.
- See which user borrowed which book.
- See active, overdue and returned records.

An administrator **cannot borrow books** because all borrowing actions require the `user` role.

## Magical interface

The interface was redesigned around an original wizarding-library aesthetic:
- Hogwarts-at-night background.
- Dark castle atmosphere.
- Parchment gold and deep violet palette.
- Cinzel / old-book style typography.
- Glass-like archive panels.
- Animated star field.
- Text-based custom logo: **THE ARCANE ARCHIVE**.
- No emoji/icon buttons.
- Responsive mobile layout.
- Themed confirmation dialog for book removal.
- Animated success/error popups for login, registration, borrowing, returning, adding and removing books.

The Hogwarts background used in this student project is the Wikimedia Commons photograph
`Hogwarts (28731128404).jpg` by HarshLight, licensed CC BY 2.0.
Source: https://commons.wikimedia.org/wiki/File:Hogwarts_(28731128404).jpg

## Extra files added
- `admin_login.php`
- `user_login.php`
- `edit_book.php`
- `delete_book.php`

## Installation with XAMPP

1. Start **Apache** and **MySQL**.
2. Copy this folder to:
   `C:\xampp\htdocs\Library_Management_System_Modern`
3. Open phpMyAdmin.
4. Import `schema.sql`.
5. Open:
   `http://localhost/Library_Management_System_Modern/setup_admin.php`
6. Default administrator:
   - Username: `admin`
   - Password: `admin123`
7. Delete `setup_admin.php` after creating the admin.
8. Open the project:
   `http://localhost/Library_Management_System_Modern/`
9. Create normal users from `register.php`.

## Project structure

```text
Library_Management_System_Modern/
│
├── assets/
│   └── style.css
│
├── config/
│   ├── auth.php
│   └── db.php
│
├── includes/
│   ├── header.php
│   └── footer.php
│
├── admin.php
├── admin_login.php
├── admin_books.php
├── admin_loans.php
├── add_book.php
├── edit_book.php
├── delete_book.php
├── user_login.php
├── login.php
├── register.php
├── dashboard.php
├── books.php
├── borrow.php
├── return.php
├── history.php
├── profile.php
├── logout.php
├── index.php
├── setup_admin.php
├── schema.sql
└── README.md
```

## Security

The project keeps the original beginner-friendly security practices:
- Prepared SQL statements.
- `password_hash()` and `password_verify()`.
- Session-based authentication.
- Role checks on protected pages.
- Escaped HTML output.
- Transaction-based borrow/return operations.

For production use, add CSRF protection, HTTPS, secure cookie flags, login rate limiting, password reset, audit logging and stronger validation.

## Borrowing workflow

- A student clicking **Request Borrow** does **not** immediately borrow the book.
- The request appears in the administrator's Borrowing Ledger as **Request Pending**.
- The administrator can **Approve** or **Decline** the request.
- Only after approval does the book become borrowed and unavailable.
- Students do **not** have a Return button.
- When a student physically returns a book, the administrator searches for the user/book in the Borrowing Ledger and marks it **Done — Returned**.
- Borrowed books have a 7-day due date.
- Overdue returns use a simple ₹10/day fine in this basic project. The administrator must confirm **User has paid the fine** before completing an overdue return.

## Important: upgrading an existing database

If you already created `library_management` using an older version of this project, do **not** rely on the updated `schema.sql` alone. `CREATE TABLE IF NOT EXISTS` does not modify an existing table.

Run `upgrade_existing_database.sql` once in phpMyAdmin SQL. It adds the `borrowings.created_at` column required by the current user request dashboard.

If you are creating the database for the first time, run `schema.sql` instead.
