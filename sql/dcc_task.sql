# 任务表
drop table if exists dcc_task;
create table dcc_task(
	id int(11) not null auto_increment,
	name varchar(60) not null default "",
	
	added_by int(11) not null default 0,
	added_date datetime default null,
	
	primary key(id)
)ENGINE=INNODB default charset=utf8mb4 comment "任务表";

insert into dcc_task (name, added_by, added_date) values ("点赞+评论（10字以上），截图上传", 0, "2021-05-29 12:12:12");