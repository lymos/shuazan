# 提现记录表
drop table if exists dcc_cashout_record;
create table dcc_cashout_record(
	id int(11) not null auto_increment,
	userid int(11) not null default 0,
	
	amount varchar(6) not null default "",
	
	added_date datetime default null,
	type tinyint(1) not null default 0 comment "0.收益 1.本金",
	
	primary key(id)
)ENGINE=INNODB default charset=utf8mb4 comment "提现记录表";
