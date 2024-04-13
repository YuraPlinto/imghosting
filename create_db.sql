-- Создание пользователя и базы данных
create user 'imghosting'@'localhost' identified by 'seyrcbybvvd_228Y343';
create database imghosting;
grant all privileges on imghosting.* to 'imghosting'@'localhost';
use imghosting;

-- Создание таблицы с информацией об изображениях
create table image(
	id           int not null auto_increment,
	name         varchar(1000) not null,
	uploaded_at  datetime not null,
	primary key (id)
) engine = InnoDB;
