# 收益表
drop table if exists dcc_user_gain;
create table dcc_user_gain(
	id int(11) not null auto_increment,
	userid int(11) not null default 0,
	
	percent tinyint(1) not null default 0,
	capital int(11) not null default 0 comment "本金",
	gain varchar(6) not null default "",
	
	gain_date date DEFAULT null,
	added_date datetime default null,
	type tinyint(1) not null default 0 comment "0.任务收益 1.邀请收益",
	
	primary key(id)
)ENGINE=INNODB default charset=utf8mb4 comment "收益表";
