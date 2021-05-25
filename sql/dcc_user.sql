# dcc_user
drop table if exists dcc_user;
create table dcc_user(
	id int(11) not null auto_increment,
	mobile varchar(11) not null default "",
	signup_code varchar(6) not null default "",
	login_code varchar(6) not null default "",
	signup_code_expire int(11) not null default 0,
	login_code_expire int(11) not null default 0,
	`type` tinyint(1) not null default 0 comment "0.user 1.admin",
	added_date datetime default null,
	
	primary key(id)
)ENGINE=INNODB default charset=utf8mb4 comment "用户表";
