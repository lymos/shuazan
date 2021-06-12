#  用户邀请关联表
drop table if exists dcc_user_invite;
create table dcc_user_invite(
	id int(11) not null auto_increment,
	userid int(11) not null default 0,
	
	invite_userid int(11) not null default 0,
	status tinyint(1) not null default 0 comment "0.普通用户 1.算收益用户",
	primary key(id),
	unique key(userid, invite_userid)
)ENGINE=INNODB default charset=utf8mb4 comment "用户邀请关联表";
