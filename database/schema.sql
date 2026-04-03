create database pothenesxes;

use pothenesxes;

CREATE TABLE positions(
position_id int PRIMARY KEY,
position_name varchar(100));

INSERT INTO positions(position_id,position_name)
VALUES ('999' , 'Citizen');

CREATE TABLE users (
user_id int AUTO_INCREMENT PRIMARY KEY,
first_name varchar(20),
last_name varchar(20),
email varchar(100) unique,
Phone varchar(8) unique,
created_at timestamp default current_timestamp,
position_id int DEFAULT '999', /*999 for citizens*/
role enum('Admin' , 'User', 'Politician'),
foreign key (position_id) references positions(position_id));

CREATE TABLE accounts(
user_id int  PRIMARY KEY,
username varchar(20),
password_hash varchar(255),
foreign key (user_id) references users(user_id) ON DELETE CASCADE);


