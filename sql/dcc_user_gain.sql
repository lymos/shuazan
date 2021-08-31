# 收益表
drop table if exists dcc_user_gain;
create table dcc_user_gain(
	id int(11) not null auto_increment,
	userid int(11) not null default 0,
	gain varchar(8) not null default "",
	added_by int(11) not null default 0,
	added_date datetime default null,
	updated_by int(11) not null default 0,
	updated_date datetime default null,
	
	primary key(id),
	unique key(userid)
)ENGINE=INNODB default charset=utf8mb4 comment "收益表";
