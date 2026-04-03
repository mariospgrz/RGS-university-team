-- Run this against the existing `pothenesxes` database to add the submissions table.
use pothenesxes;

CREATE TABLE IF NOT EXISTS submissions (
    submission_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id       INT NOT NULL,
    year          INT NOT NULL,
    submitted_at  DATETIME DEFAULT CURRENT_TIMESTAMP,
    status        ENUM('Pending', 'Approved', 'Rejected') DEFAULT 'Pending',
    notes         TEXT,
    FOREIGN KEY (user_id) REFERENCES users(user_id)
);
