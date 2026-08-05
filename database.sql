-- Create and select the miniFacebook database.

CREATE DATABASE IF NOT EXISTS minifacebook;

USE minifacebook;


-- Store regular users and superusers.

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,

    email VARCHAR(100)
        NOT NULL
        UNIQUE,

    password VARCHAR(255)
        NOT NULL,

    name VARCHAR(100)
        NOT NULL,

    additional_email VARCHAR(100),

    phone VARCHAR(20),

    role ENUM('user', 'superuser')
        NOT NULL
        DEFAULT 'user',

    is_disabled TINYINT(1)
        NOT NULL
        DEFAULT 0,

    created_at TIMESTAMP
        DEFAULT CURRENT_TIMESTAMP
);


-- Store posts created by users.

CREATE TABLE posts (
    id INT AUTO_INCREMENT PRIMARY KEY,

    user_id INT NOT NULL,

    content TEXT NOT NULL,

    created_at TIMESTAMP
        DEFAULT CURRENT_TIMESTAMP,

    updated_at TIMESTAMP
        DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON DELETE CASCADE
);

CREATE TABLE comments (
    id INT AUTO_INCREMENT PRIMARY KEY,

    post_id INT NOT NULL,

    user_id INT NOT NULL,

    content TEXT NOT NULL,

    created_at TIMESTAMP
        DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (post_id)
        REFERENCES posts(id)
        ON DELETE CASCADE,

    FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON DELETE CASCADE
);
-- Store live chat messages.

CREATE TABLE chat_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,

    user_id INT NOT NULL,

    message VARCHAR(500) NOT NULL,

    created_at TIMESTAMP
        DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON DELETE CASCADE
);
