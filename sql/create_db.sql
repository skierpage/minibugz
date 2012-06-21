-- Run this once, probably as root DB user, e.g. with
-- % mysql -u root -p
-- source create_db.sql
create database minibugz;
grant index, create, select, insert, update, delete, alter, lock tables on minibugz.* to 'minibugz_user'@'localhost' identified by 'pwd';

