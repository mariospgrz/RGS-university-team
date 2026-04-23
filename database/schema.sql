create database pothenesxes;

use pothenesxes;

CREATE TABLE positions(
                          position_id int PRIMARY KEY,
                          position_name varchar(100));

INSERT INTO positions(position_id,position_name)
VALUES ('999' , 'Citizen');

CREATE TABLE parties (
                         party_id INT AUTO_INCREMENT PRIMARY KEY,
                         party_name VARCHAR(100) NOT NULL UNIQUE,
                         party_acronym VARCHAR(20)
);

CREATE TABLE users (
                       user_id int AUTO_INCREMENT PRIMARY KEY,
                       first_name varchar(20),
                       last_name varchar(20),
                       email varchar(100) unique,
                       Phone varchar(8) unique,
                       created_at timestamp default current_timestamp,
                       position_id int DEFAULT '999', /*999 for citizens*/
                       party_id int NULL,
                       role enum('Admin' , 'User', 'Politician'),
                       profile_picture varchar(255) NOT NULL DEFAULT '../Assets/media/profile_placeholder.png',
                       foreign key (position_id) references positions(position_id),
                       foreign key (party_id) references parties(party_id) ON DELETE SET NULL);


CREATE TABLE accounts(
                         user_id int  PRIMARY KEY,
                         username varchar(20),
                         password_hash varchar(255),
                         foreign key (user_id) references users(user_id) ON DELETE CASCADE);

CREATE TABLE submissions (
                             submission_id INT AUTO_INCREMENT PRIMARY KEY,
                             user_id       INT NOT NULL,
                             year          INT NOT NULL,
                             submitted_at  DATETIME DEFAULT CURRENT_TIMESTAMP,
                             status        ENUM('Pending', 'Approved', 'Rejected') DEFAULT 'Pending',
                             pdf_path      VARCHAR(255) DEFAULT NULL,
                             notes         TEXT,
                             FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);
