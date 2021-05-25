# 订单明细表
drop table if exists dcc_order_detail;
create table dcc_order_detail(
	id int(11) not null auto_increment,
	orderid int(11) not null default 0,
	productid int(11) not null default 0,
	
	primary key(id)
)ENGINE=INNODB default charset=utf8mb4 comment "订单明细表";
