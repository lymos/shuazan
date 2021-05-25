#  用户任务关联表
drop table if exists dcc_user_task;
create table dcc_user_task(
	id int(11) not null auto_increment,
	userid int(11) not null default 0,
	
	taskid int(11) not null default 0,
	
	primary key(id)
)ENGINE=INNODB default charset=utf8mb4 comment "用户任务关联表";
