#  银行卡表
drop table if exists dcc_user_card;
create table dcc_user_card(
	id int(11) not null auto_increment,
	userid int(11) not null default 0,
	name varchar(20) not null default "",
	number varchar(60) not null default "",
	brand varchar(25) not null default "",
	mibile varchar(11) not null default "",
	
	primary key(id)
)ENGINE=INNODB default charset=utf8mb4 comment "银行卡表";
