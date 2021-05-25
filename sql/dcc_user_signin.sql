#  签到表
drop table if exists dcc_user_signin;
create table dcc_user_signin(
	id int(11) not null auto_increment,
	userid int(11) not null default 0,
	
	signin_date date default null,
	
	primary key(id)
)ENGINE=INNODB default charset=utf8mb4 comment "签到表";
