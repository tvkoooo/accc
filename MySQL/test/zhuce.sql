use easydb;
/* Procedure structure for procedure `p_easydb_zhuce` */
/*!50003 DROP PROCEDURE IF EXISTS  `p_easydb_zhuce` */;
DELIMITER $$ 
/*!50003 CREATE DEFINER=`root`@`localhost` PROCEDURE `p_easydb_zhuce`(
	OUT `_COMBACK` char(20),
	IN `_user_name` char(20),
	IN `_user_password` char(20)
	)
BEGIN

declare a INT;
select count(t_easytab.user_name) into a FROM easydb.t_easytab where user_name=_user_name;

if a='0'
THEN
INSERT INTO t_easytab (user_name,user_password,comefrom,money,sun) VALUES (_user_name,_user_password,'beijing','1000','0');
SET _COMBACK='Success';
else
SET _COMBACK='Fail';
END IF;
	/*成功*/
END */$$
DELIMITER ;
