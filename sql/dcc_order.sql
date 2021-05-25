# 订单表
drop table if exists dcc_order;
create table dcc_order(
	id int(11) not null auto_increment,
	total varchar(10) not null default "",
	userid int(11) not null default 0,
	added_by int(11) not null default 0,
	added_date datetime default null,
	
	primary key(id)
)ENGINE=INNODB default charset=utf8mb4 comment "订单表";
