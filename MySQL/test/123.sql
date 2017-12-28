
insert into t_user_log(user_name,user_aplydate,user_joindate) select user_name,logdate,last_2date from t_easytab;

select * from t_easytab;
select * from t_user_log;
select * from v_user_log;
select * from v_user_all;

select t_easytab.*,t_user_log.* from t_easytab inner join t_user_log on t_easytab.user_name=t_user_log.user_name;
create view v_user_all as select t_easytab.user_name,t_easytab.user_password,t_easytab.comefrom,t_easytab.money,t_easytab.sun,
t_user_log.user_aplydate,t_user_log.user_joindate
from t_easytab inner join t_user_log on t_easytab.user_name=t_user_log.user_name;

create view v_user_log as select user_name,user_aplydate,user_joindate,user_joindate-user_aplydate as 次日访问 from t_user_log;

alter table `t_easytab` drop column lastdate;

select * from t_easytab ORDER BY user_name ASC;
set @next_money=-1; /*两行相减*/
select @next_money,if(@next_money<0,null,money-@next_money) as result,@next_money:=money 
from t_easytab;
 
select money-@next_money,@next_money:=money from t_easytab ;

create view view_easytab as select lastdate,last_2date,lastdate-last_2date as 次日访问 from t_easytab;
select * from view_easytab;
select *from view_easytab where (次日访问<'3');

select lastdate,last_2date,lastdate-last_2date as 次日访问 from t_easytab;
select *,lastdate-last_2date as 次日访问 from t_easytab where (lastdate-last_2date<'3');
select *,lastdate-last_2date as 次日访问 from t_easytab where (lastdate-last_2date='1' and user_name='5');
select *,lastdate-last_2date as 次日访问 from t_easytab where (user_name='5');

