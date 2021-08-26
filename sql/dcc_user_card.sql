#  银行卡表
drop table if exists dcc_user_card;
create table dcc_user_card(
	id int(11) not null auto_increment,
	userid int(11) not null default 0,
	name varchar(20) not null default "",
	card varchar(60) not null default "",
	brand varchar(25) not null default "",
	mobile varchar(11) not null default "",
	wx_qrcode varchar(60) not null default "",
	ali_qrcode varchar(60) not null default "",
	added_by int(11) not null default 0,
	added_date datetime default null,
	updated_by int(11) not null default 0,
	updated_date datetime default null,
	
	
	primary key(id),
	unique key(card)
)ENGINE=INNODB default charset=utf8mb4 comment "银行卡表";
