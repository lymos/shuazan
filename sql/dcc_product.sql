# 产品表
drop table if exists dcc_task;
create table dcc_task(
	id int(11) not null auto_increment,
	name varchar(60) not null default "",
	price varchar(10) not null default "",
	added_by int(11) not null default 0,
	added_date datetime default null,
	
	primary key(id)
)ENGINE=INNODB default charset=utf8mb4 comment "产品表";
