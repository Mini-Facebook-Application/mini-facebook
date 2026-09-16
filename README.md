# MiniFacebook – Full-Stack Social Networking Application

A secure, database-driven social networking application built with PHP and MySQL. MiniFacebook supports user authentication, profile management, user-generated posts, comments, administrative account controls, and role-based authorization.

[Project Website](https://mini-facebook-application.github.io) · [Video Demonstration](https://youtu.be/LPuJboEGtTM)

## Project Overview

MiniFacebook is a full-stack web application that allows users to create accounts, manage profiles, publish posts, and interact through comments. The application also provides administrative tools for reviewing registered users and controlling account access.

The project demonstrates full-stack software development, relational database design, CRUD operations, authentication and authorization, secure session management, server-side input validation, web security, and deployment in an Ubuntu environment.

## Project Structure

```text
mini-facebook/
├── index.php
├── login.php
├── register.php
├── profile.php
├── posts.php
├── comments.php
├── admin/
│   └── users.php
├── config/
│   └── database.php
├── assets/
│   ├── css/
│   └── images/
└── database/
    └── schema.sql
```

### File and Directory Overview

| File or Directory     | Purpose                                                                   |
| --------------------- | ------------------------------------------------------------------------- |
| `index.php`           | Displays the main application page and user-generated posts               |
| `login.php`           | Authenticates users and initializes secure sessions                       |
| `register.php`        | Validates registration information and creates user accounts              |
| `profile.php`         | Displays and updates user profile information                             |
| `posts.php`           | Handles post creation, retrieval, editing, deletion, and ownership checks |
| `comments.php`        | Processes comments and connects them to users and posts                   |
| `admin/`              | Contains protected superuser account-management functionality             |
| `config/database.php` | Establishes the application’s MySQL database connection                   |
| `assets/css/`         | Stores application stylesheets                                            |
| `assets/images/`      | Stores images and other visual assets                                     |
| `database/schema.sql` | Defines the relational database schema                                    |

## Key Features

### User Accounts

* Register with an email address and password
* Authenticate through a secure login system
* Log out and terminate the active session
* View and update profile information
* Change account passwords
* Prevent disabled accounts from authenticating

### Posts and Comments

* View posts and associated comments
* Create new posts
* Edit and delete owned posts
* Comment on posts created by other users
* Enforce post ownership before allowing modifications

### Administration

* Authenticate with a dedicated superuser role
* View registered user accounts
* Disable user accounts
* Re-enable previously disabled accounts
* Restrict administrative operations through role-based access control

## Technical Highlights

* Developed the client-facing interface and server-side application using PHP, HTML5, CSS3, and Bootstrap
* Designed a relational MySQL database for users, posts, and comments
* Implemented create, read, update, and delete operations across the application
* Built user registration, authentication, profile, and password-management workflows
* Applied role-based access control for regular users and superusers
* Enforced resource ownership to prevent unauthorized post modification
* Used prepared SQL statements to protect database operations
* Added CSRF protection, server-side validation, and output encoding
* Configured secure session handling and inactivity expiration
* Deployed the application with Apache in an Ubuntu environment
* Managed source code and project documentation with Git and GitHub

## Technology Stack

| Category              | Technologies                                                                                                    |
| --------------------- | --------------------------------------------------------------------------------------------------------------- |
| Front End             | HTML5, CSS3, Bootstrap                                                                                          |
| Back End              | PHP                                                                                                             |
| Database              | MySQL, SQL                                                                                                      |
| Web Server            | Apache                                                                                                          |
| Operating Environment | Ubuntu Linux                                                                                                    |
| Security              | Password hashing, prepared statements, CSRF tokens, output encoding, secure sessions, role-based access control |
| Deployment            | Apache, HTTPS, SSL/TLS                                                                                          |
| Development Tools     | Git, GitHub                                                                                                     |

## Application Architecture

The application uses a server-rendered, database-backed architecture.

| Layer                | Responsibility                                                                      |
| -------------------- | ----------------------------------------------------------------------------------- |
| Presentation layer   | Displays registration forms, profiles, posts, comments, and administrative controls |
| Application layer    | Processes requests, validates input, manages sessions, and enforces authorization   |
| Data layer           | Stores and retrieves users, posts, comments, roles, and account statuses            |
| Infrastructure layer | Runs the PHP application through Apache in an Ubuntu environment                    |

## Database Design

The MySQL database contains three primary relational tables.

### `users`

Stores:

* Account credentials
* Profile information
* User roles
* Account status

### `posts`

Stores:

* Post content
* Author information
* Relationships between posts and registered users

### `comments`

Stores:

* Comment content
* Comment authors
* Relationships between comments, posts, and users

Foreign-key relationships connect posts and comments to their owners and maintain referential integrity across the application.

## Authentication and Authorization

MiniFacebook separates permissions between regular users and superusers.

### Regular Users

Regular users can:

* Manage their profiles
* Change their passwords
* Create posts
* Edit or delete their own posts
* Comment on shared content

### Superusers

Superusers can:

* View registered accounts
* Disable user access
* Re-enable user access
* Perform protected administrative operations

Server-side authorization and ownership checks prevent users from modifying resources that do not belong to them.

## Security

Security controls are applied to authentication, database access, forms, sessions, authorization, and user-generated content.

Implemented protections include:

* Password hashing with `password_hash()`
* Password verification with `password_verify()`
* Parameterized queries using prepared SQL statements
* Server-side input validation
* Output encoding with `htmlspecialchars()`
* Cross-site scripting mitigation
* CSRF tokens on protected forms
* Secure session-cookie configuration
* Session ID regeneration after authentication
* Automatic session expiration after inactivity
* Browser-session verification
* Role-based access control
* Post-ownership enforcement
* A dedicated application database account instead of MySQL root access
* HTTPS deployment with SSL/TLS encryption

## Runtime Requirements

The complete application requires a server-side environment because PHP and MySQL cannot run through GitHub Pages.

The application was designed to run with:

* Ubuntu Linux or a comparable Linux environment
* Apache HTTP Server
* PHP
* MySQL
* A modern web browser

The public GitHub Pages website provides the project documentation. The video demonstration shows the functionality of the complete PHP and MySQL application.

## Testing

The primary user, administrative, database, and security workflows were manually tested.

Testing covered:

* User registration
* Successful and unsuccessful login attempts
* Secure logout
* Profile updates
* Password changes
* Post creation, editing, and deletion
* Comments on posts from other users
* Post-ownership authorization
* Input validation
* Output escaping against XSS payloads
* CSRF-protected form submissions
* Superuser authentication
* Registered-user management
* Account disabling and re-enabling
* Prevention of login attempts from disabled accounts
* Session expiration behavior

## Project Links

* **Project website:** [mini-facebook-application.github.io](https://mini-facebook-application.github.io)
* **Video demonstration:** [YouTube](https://youtu.be/LPuJboEGtTM)

## Author

**Hinna Parwez**
[parwezhs@mail.uc.edu](mailto:parwezhs@mail.uc.edu)
