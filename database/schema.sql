CREATE TABLE users (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,

    avatar TEXT NULL,

    name VARCHAR(100) NOT NULL,

    username VARCHAR(30) NOT NULL UNIQUE,

    email VARCHAR(100) NOT NULL UNIQUE,

    phone VARCHAR(20) NULL,

    gender ENUM('Male','Female','Other') NULL,

    birth_date DATE NULL,

    bio TEXT NULL,

    city VARCHAR(100) NULL,

    password VARCHAR(255) NOT NULL,

    role ENUM('USER','ADMIN') DEFAULT 'USER',

    status ENUM('ACTIVE','BLOCKED','DEACTIVATED') DEFAULT 'ACTIVE',

    is_email_verified BOOLEAN DEFAULT FALSE,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE blogs (

    id BIGINT PRIMARY KEY AUTO_INCREMENT,

    user_id BIGINT NOT NULL,

    title VARCHAR(255) NOT NULL,

    slug VARCHAR(255) NOT NULL UNIQUE,

    description TEXT NOT NULL,

    category VARCHAR(100) NOT NULL,

    hashtags TEXT NULL,

    visibility ENUM('PUBLIC', 'PRIVATE')
    DEFAULT 'PUBLIC',

    status ENUM(
        'ACTIVE',
        'RESTRICTED',
        'DELETED'
    ) DEFAULT 'ACTIVE',

    restricted_reason TEXT NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_blog_user
    FOREIGN KEY (user_id)
    REFERENCES users(id)
    ON DELETE CASCADE

);

CREATE TABLE uploads (

    id BIGINT PRIMARY KEY AUTO_INCREMENT,

    blog_id BIGINT NOT NULL,

    type ENUM('IMAGE', 'VIDEO')
    NOT NULL,

    url TEXT NOT NULL,

    public_id VARCHAR(255) NOT NULL,

    folder VARCHAR(255) NULL,

    filename VARCHAR(255) NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_upload_blog
    FOREIGN KEY (blog_id)
    REFERENCES blogs(id)
    ON DELETE CASCADE

);

CREATE TABLE comments (

    id BIGINT PRIMARY KEY AUTO_INCREMENT,

    blog_id BIGINT NOT NULL,

    user_id BIGINT NOT NULL,

    comment TEXT NOT NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_comment_blog
    FOREIGN KEY (blog_id)
    REFERENCES blogs(id)
    ON DELETE CASCADE,

    CONSTRAINT fk_comment_user
    FOREIGN KEY (user_id)
    REFERENCES users(id)
    ON DELETE CASCADE

);

CREATE TABLE likes (

    id BIGINT PRIMARY KEY AUTO_INCREMENT,

    blog_id BIGINT NOT NULL,

    user_id BIGINT NOT NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_like_blog
    FOREIGN KEY (blog_id)
    REFERENCES blogs(id)
    ON DELETE CASCADE,

    CONSTRAINT fk_like_user
    FOREIGN KEY (user_id)
    REFERENCES users(id)
    ON DELETE CASCADE,

    CONSTRAINT unique_user_blog_like
    UNIQUE(user_id, blog_id)

);

CREATE TABLE password_resets (

    id BIGINT PRIMARY KEY AUTO_INCREMENT,

    user_id BIGINT NOT NULL,

    token TEXT NOT NULL,

    expired_at TIMESTAMP NOT NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_password_reset_user
    FOREIGN KEY (user_id)
    REFERENCES users(id)
    ON DELETE CASCADE

);

CREATE TABLE otp_verifications (

    id BIGINT PRIMARY KEY AUTO_INCREMENT,

    user_id BIGINT NOT NULL,

    otp VARCHAR(10) NOT NULL,

    expired_at TIMESTAMP NOT NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_otp_user
    FOREIGN KEY (user_id)
    REFERENCES users(id)
    ON DELETE CASCADE

);