# 收益表
drop table if exists dcc_user_gain;
create table dcc_user_gain(
	id int(11) not null auto_increment,
	userid int(11) not null default 0,
	
	percent varchar(6) not null default "",
	capital varchar(10) not null default 0 comment "本金",
	gain varchar(6) not null default "",
	
	gain_date date DEFAULT null,
	added_date datetime default null,
	type tinyint(1) not null default 0 comment "0.任务收益 1.邀请收益",
	status tinyint(1) not null default 0 comment "0.未提现 1.已提现",
	cashout_by int(11) not null default 0,
	cashout_date datetime default null comment "提现时间",
	
	primary key(id),
	unique key(userid, gain_date, type)
)ENGINE=INNODB default charset=utf8mb4 comment "收益表";
