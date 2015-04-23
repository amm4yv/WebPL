DROP DATABASE Allison;
CREATE DATABASE Allison;
GRANT ALL on Allison.* to Allison@localhost IDENTIFIED by 'charlie';
USE Allison;
CREATE TABLE Sender (Sender_id int primary key not null, Sender_name text not null, Sender_email text not null);
CREATE TABLE Admin (Admin_id int primary key not null, Name text not null, Email text not null, Password text not null);
CREATE TABLE Tickets (Ticket_num int primary key not null, Received timestamp not null, Sender_id int not null, Subject text not null, Admin_id int, Status text not null, Message text not null, FOREIGN KEY (Sender_id) REFERENCES Sender(Sender_id), FOREIGN KEY (Admin_id) REFERENCES Admin(Admin_id));