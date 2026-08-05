# waph-teamproject

A secure miniFacebook web application built with PHP and MySQL.

## Project Links

- Application: https://waph-team03.minifacebook.com
- Team website: `https://waph-uc-sm26-team03.github.io/`
- Video demonstration: `Add video link`
- Private repository: `https://github.com/waph-uc-sm26-team03/waph-teamproject`

---

# HTTPS Deployment

The application is deployed using HTTPS.

![HTTPS Team Domain Demo](waphtestpage.png)

---

# Application Features

## Regular Users

Registered users can:

- Register using an email and password.
- Log in and log out.
- Change their password.
- Edit their profile information.
- View posts and comments.
- Create new posts.
- Edit their own posts.
- Delete their own posts.
- Comment on any post.

## Superusers

Superusers can:

- Log in using an account promoted directly in MySQL.
- View all registered users.
- Disable regular-user accounts.
- Enable regular-user accounts.

Disabled users cannot log in or continue using an existing session.

---

# Database Design

The application uses three main tables:

## Users

Stores:

- Email and hashed password
- Name
- Additional email
- Phone number
- User role
- Disabled-account status
- Account creation time

## Posts

Stores:

- Post content
- Post owner
- Creation time
- Update time

## Comments

Stores:

- Comment content
- Related post
- Comment author
- Creation time

The database relationships use foreign keys. Deleting a user or post also removes the related records using `ON DELETE CASCADE`.

---

# Security Requirements

## Password Security

Passwords are never stored as plain text.

The application uses:

- `password_hash()` when creating or changing a password
- `password_verify()` during login

## SQL Injection Protection

All queries that use application or user data are implemented with prepared statements.

## Input Validation

Input is validated at multiple levels:

- HTML attributes such as `required`, `maxlength`, and `minlength`
- PHP validation using functions such as `filter_var()` and `preg_match()`
- Prepared SQL statements and database field restrictions

## XSS Protection

Dynamic content is escaped before being displayed using the shared `e()` function and `htmlspecialchars()`.

This prevents submitted HTML and JavaScript from executing in the browser.

## CSRF Protection

Forms that change application data include a random CSRF token.

The server verifies the token before processing actions such as:

- Creating posts
- Editing posts
- Deleting posts
- Adding comments
- Editing profiles
- Changing passwords
- Enabling or disabling users
- Logging out

## Session Security

The application:

- Regenerates the session ID after login
- Uses HttpOnly session cookies
- Uses Secure cookies when HTTPS is active
- Uses `SameSite=Strict`
- Ends sessions after 30 minutes of inactivity
- Compares browser information to help detect session hijacking

## Role-Based Access Control

The application separates regular users and superusers.

- Regular users cannot access the superuser page.
- Superuser accounts cannot be created through registration.
- Superusers are promoted directly in MySQL.
- Only superusers can enable or disable accounts.

## Post Ownership

The edit and delete SQL queries include both the post ID and logged-in user ID.

This prevents users from editing or deleting posts belonging to someone else.

## Database Account

The PHP application uses the dedicated `waphuser` MySQL account instead of the MySQL root account.

## Front-End Template

The application integrates the open-source Bootstrap 5.3.3 CSS framework with additional styling in `style.css`.

---

# Database Installation

## Fresh Installation

Run `database.sql` to create a new database.

## Upgrade from Sprint 1

Run `migration_sprint2.sql` once to update the existing Sprint 1 database.

The migration adds:

- User roles
- Disabled-account status
- Post update timestamps
- The comments table

## Creating a Superuser

First, register the account normally so its password is securely hashed.

Then run:

```sql
UPDATE users
SET role = 'superuser'
WHERE email = 'admin@example.com';
