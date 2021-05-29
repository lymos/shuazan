#  签到表
drop table if exists dcc_user_signin;
create table dcc_user_signin(
	id int(11) not null auto_increment,
	userid int(11) not null default 0,
	
	signin_date date default null,
	days tinyint(1) not null default 1 comment "连续天数",
	status tinyint(1) not null default 0 comment "0.未结算 1.已结算",
	
	primary key(id),
	unique key (userid, signin_date)
)ENGINE=INNODB default charset=utf8mb4 comment "签到表";
