# 订单表
drop table if exists dcc_order;
create table dcc_order(
	id int(11) not null auto_increment,
	orderid varchar(16) not null default "",
	total varchar(10) not null default "",
	userid int(11) not null default 0,
	`status` tinyint(1) not null default 0 comment "0.待付款 1.success, 2.付款失败 3.已退款 4.已取消",
	pay_type tinyint(1) not null default 0 comment "1.alipay 2.wxpay 3.unionpay",
	added_by int(11) not null default 0,
	added_date datetime default null,
	updated_by int(11) not null default 0,
	updated_date datetime default null,
	
	primary key(id),
	unique key(orderid),
	key (total)
)ENGINE=INNODB default charset=utf8mb4 comment "订单表";
