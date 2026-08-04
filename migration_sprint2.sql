USE minifacebook;

ALTER TABLE users
    ADD COLUMN role
        ENUM('user', 'superuser')
        NOT NULL
        DEFAULT 'user',

    ADD COLUMN is_disabled
        TINYINT(1)
        NOT NULL
        DEFAULT 0;

ALTER TABLE posts
    ADD COLUMN updated_at
        TIMESTAMP
        DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP;

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
