# 收益明细表
drop table if exists dcc_user_gain_detail;
create table dcc_user_gain_detail(
	id int(11) not null auto_increment,
	userid int(11) not null default 0,
	
	percent varchar(6) not null default "",
	capital varchar(10) not null default 0 comment "本金",
	gain varchar(8) not null default "",
	
	gain_date date DEFAULT null,
	added_by int(11) not null default 0,
	added_date datetime default null,
	updated_by int(11) not null default 0,
	updated_date datetime default null,
	type tinyint(1) not null default 0 comment "0.任务收益 1.邀请收益, 2.总收益",
	
	primary key(id),
	unique key(userid, gain_date, type)
)ENGINE=INNODB default charset=utf8mb4 comment "收益明细表";
