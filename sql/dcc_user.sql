# dcc_user
drop table if exists dcc_user;
create table dcc_user(
	id int(11) not null auto_increment,
	mobile varchar(60) not null default "",
	pwd varchar(60) not null default "",
	signup_code varchar(6) not null default "",
	login_code varchar(6) not null default "",
	signup_code_expire int(11) not null default 0,
	login_code_expire int(11) not null default 0,
	token varchar(60) not null default "",
	token_expire int(11) not null default 0,
	invite_code varchar(6) not null default "",
	sms_ip varchar(60) not null default "",
	sms_times tinyint(1) not null default 0,
	sms_last_date datetime default null,
	`type` tinyint(1) not null default 0 comment "0.user 1.admin 2.签到赠送",
	`status` tinyint(1) not null default 0 comment "0.未成功 1.success",
	added_date datetime default null,
	updated_by int(11) not null default 0,
	updated_date datetime default null,
	
	primary key(id),
	unique key(mobile)
)ENGINE=INNODB default charset=utf8mb4 comment "用户表";

insert into dcc_user (mobile, pwd, invite_code, added_date, type, status) values ("15323454221", md5(md5('lymosDcc.123-dcc')), "1234567", "2021-05-28 12:12:12", 1, 1);
insert into dcc_user (mobile, pwd, invite_code, added_date, type, status) values ("17317340417", md5(md5('Dcc.88880417-dcc')), "123456", "2021-05-28 12:12:12", 1, 1);
