CREATE DATABASE IF NOT EXISTS library_management;
USE library_management;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(120) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('user', 'admin') NOT NULL DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS books (
    id INT AUTO_INCREMENT PRIMARY KEY,
    accession_number VARCHAR(50) NOT NULL UNIQUE,
    title VARCHAR(200) NOT NULL,
    author VARCHAR(150) NOT NULL,
    edition VARCHAR(50) NOT NULL,
    publisher VARCHAR(150) NOT NULL,
    available TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS borrowings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    book_id INT NOT NULL,
    borrowed_at DATETIME NULL,
    due_date DATETIME NULL,
    approved_at DATETIME NULL,
    returned_at DATETIME NULL,
    status ENUM('pending', 'borrowed', 'returned') NOT NULL DEFAULT 'pending',
    fine_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    fine_paid TINYINT(1) NOT NULL DEFAULT 0,

    CONSTRAINT fk_borrow_user
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_borrow_book
        FOREIGN KEY (book_id) REFERENCES books(id)
        ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE INDEX idx_borrow_user_status ON borrowings(user_id, status);
CREATE INDEX idx_borrow_book_status ON borrowings(book_id, status);
CREATE INDEX idx_books_title ON books(title);
CREATE INDEX idx_books_author ON books(author);
