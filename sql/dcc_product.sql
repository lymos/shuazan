# 产品表
drop table if exists dcc_product;
create table dcc_product(
	id int(11) not null auto_increment,
	name varchar(60) not null default "",
	price varchar(10) not null default "",
	added_by int(11) not null default 0,
	added_date datetime default null,
	updated_by int(11) not null default 0,
	updated_date datetime default null,
	primary key(id)
)ENGINE=INNODB default charset=utf8mb4 comment "产品表";

insert into dcc_product (name, price, added_by, added_date) values ("抖音自动服务AI1", "1800", 0, "2021-05-28 12:12:12");
insert into dcc_product (name, price, added_by, added_date) values ("抖音自动服务AI2", "1800", 0, "2021-05-28 12:12:12");
insert into dcc_product (name, price, added_by, added_date) values ("抖音自动服务AI3", "1800", 0, "2021-05-28 12:12:12");
insert into dcc_product (name, price, added_by, added_date) values ("抖音自动服务AI4", "1800", 0, "2021-05-28 12:12:12");
insert into dcc_product (name, price, added_by, added_date) values ("抖音自动服务AI5", "1800", 0, "2021-05-28 12:12:12");
insert into dcc_product (name, price, added_by, added_date) values ("抖音自动服务AI6", "1800", 0, "2021-05-28 12:12:12");
insert into dcc_product (name, price, added_by, added_date) values ("抖音自动服务AI7", "1800", 0, "2021-05-28 12:12:12");
insert into dcc_product (name, price, added_by, added_date) values ("抖音自动服务AI8", "1800", 0, "2021-05-28 12:12:12");
insert into dcc_product (name, price, added_by, added_date) values ("抖音自动服务AI9", "1800", 0, "2021-05-28 12:12:12");
insert into dcc_product (name, price, added_by, added_date) values ("抖音自动服务AI10", "1800", 0, "2021-05-28 12:12:12");