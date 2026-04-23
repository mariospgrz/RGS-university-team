-- Run this against the existing `pothenesxes` database to add the submissions table.
use pothenesxes;

CREATE TABLE IF NOT EXISTS submissions (
    submission_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id       INT NOT NULL,
    year          INT NOT NULL,
    submitted_at  DATETIME DEFAULT CURRENT_TIMESTAMP,
    status        ENUM('Pending', 'Approved', 'Rejected') DEFAULT 'Pending',
    pdf_path      VARCHAR(255) DEFAULT NULL,
    notes         TEXT,
    FOREIGN KEY (user_id) REFERENCES users(user_id)
);

ALTER TABLE submissions
    ADD COLUMN IF NOT EXISTS pdf_path VARCHAR(255) DEFAULT NULL AFTER status;
