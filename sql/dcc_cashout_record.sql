# 提现记录表
drop table if exists dcc_cashout_record;
create table dcc_cashout_record(
	id int(11) not null auto_increment,
	userid int(11) not null default 0,
	amount varchar(6) not null default "",
	status tinyint(1) not null default 0 comment "0.待支付 1.已支付 2.废弃",
	fee_percent varchar(6) not null default "" comment "费率",
	fee varchar(6) not null default "" comment "手续费",
	
	before_total varchar(6) not null default "" comment "提现之前总额",
	after_total varchar(6) not null default "" comment "提现之后总额",
	
	added_by int(11) not null default 0,
	added_date datetime default null,
	updated_by int(11) not null default 0,
	updated_date datetime default null,

	type tinyint(1) not null default 0 comment "0.收益提现 1.本金提现",
	
	primary key(id)
)ENGINE=INNODB default charset=utf8mb4 comment "提现记录表";
