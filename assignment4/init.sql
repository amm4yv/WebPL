DROP DATABASE Allison;
CREATE DATABASE Allison;
GRANT ALL on Allison.* to Allison@localhost IDENTIFIED by 'charlie';
USE Allison;
CREATE TABLE Words (word text not null);