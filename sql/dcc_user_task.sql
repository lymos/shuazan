#  用户任务关联表
drop table if exists dcc_user_task;
create table dcc_user_task(
	id int(11) not null auto_increment,
	userid int(11) not null default 0,
	
	taskid int(11) not null default 0,
	`date` date default null,
	process tinyint(1) not null default 0 comment "满进度100",
	status tinyint(1) not null default 0 comment "0.进行中 1.已完成",
	added_date datetime default null,
	updated_date datetime default null,
	
	primary key(id),
	unique key(userid, taskid, `date`)
)ENGINE=INNODB default charset=utf8mb4 comment "用户任务关联表";
