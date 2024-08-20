PRAGMA foreign_keys = ON;
DROP TABLE IF EXISTS user;
CREATE TABLE user (
    id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
    username VARCHAR NOT NULL,
    password VARCHAR NOT NULL,
    created_at VARCHAR NOT NULL,
    is_enabled BOOLEAN NOT NULL DEFAULT true,
    role VARCHAR NOT NULL DEFAULT 'user'
);

/* This will become user = 1. I'm creating this just to satisfy constraints here.
   The password will be properly hashed in the installer */
INSERT INTO
    user
    (
        username, password, created_at, is_enabled, role
    )
    VALUES
    (
        "admin", "unhashed-password", datetime('now', '-3 months'), 1, 'admin'
    )
;

INSERT INTO
    user
    (
        username, password, created_at, is_enabled, role
    )
    VALUES
    (
        "anonymous", "robot-eres-formidable", datetime('now', '-3 months'), 1, 'anonymous'
    )
;


DROP TABLE IF EXISTS post;
CREATE TABLE post (
    id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
    title VARCHAR NOT NULL,
    body VARCHAR NOT NULL,
    image BLOB,
    thumbnail BLOB,
    user_id INTEGER NOT NULL,
    created_at VARCHAR NOT NULL,
    updated_at VARCHAR,
    FOREIGN KEY (user_id) REFERENCES user(id)
);

DROP TABLE IF EXISTS comment;
CREATE TABLE comment (
    id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
    body VARCHAR NOT NULL,
    post_id INTEGER NOT NULL,
    user_name VARCHAR NOT NULL,
    created_at VARCHAR NOT NULL,
    website VARCHAR,
    image BLOB,
    FOREIGN KEY (post_id) REFERENCES post(id)
);

DROP TABLE IF EXISTS profile;
CREATE TABLE profile (
    user_id INTEGER PRIMARY KEY,
    username VARCHAR NOT NULL,
    visibleName VARCHAR UNIQUE NOT NULL,
    aboutMe TEXT,
    website TEXT,
    equippedBadge INTEGER DEFAULT 0,
    badges TEXT, -- JSON string for array of integers
    avatar BLOB, -- For storing the image
    FOREIGN KEY (user_id) REFERENCES user(id)
);

INSERT INTO
    profile
    (
        user_id, username, visibleName, aboutMe, website, equippedBadge, badges, avatar
    )
    VALUES
    (
        1, "admin", "Admin", "I'm the admin of this site", "https://example.com", 0, "[]", NULL
    )



