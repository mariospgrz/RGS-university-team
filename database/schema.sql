create database pothenesxes;

use pothenesxes;

CREATE TABLE users (
user_id int PRIMARY KEY,
first_name varchar(20),
last_name varchar(20),
email varchar(100) unique,
Phone varchar(8) unique,
role enum('Admin' , 'User', 'Politician'));

CREATE TABLE accounts(
user_id int PRIMARY KEY,
username varchar(20),
password_hash varchar(255),
foreign key (user_id) references users(user_id));

CREATE TABLE positions(
position_id int PRIMARY KEY,
position_name varchar(100));

CREATE TABLE govOfficers(
user_id int PRIMARY KEY,
officer_id int UNIQUE,
officer_position int,
foreign key (officer_position) references positions(position_id),
foreign key (user_id) references users(user_id));
