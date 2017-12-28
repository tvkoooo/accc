use easydb;
/* Procedure structure for procedure `p_easydb_easytab_denglu` */

/*!50003 DROP PROCEDURE IF EXISTS  `p_easydb_easytab_denglu` */;

DELIMITER $$

/*!50003 CREATE DEFINER=`root`@`localhost` PROCEDURE `p_easydb_easytab_denglu`(
	OUT `_COMBACK` char(20),
	IN `_user_name` char(20),
	IN `_user_password` char(20)
	)
BEGIN

declare if_a INT;
select count(t_easytab.user_name) into if_a FROM easydb.t_easytab where user_name=_user_name and user_password=_user_password;

if if_a='1'
THEN
SET _COMBACK='Success LOG IN';
else
SET _COMBACK='Fail,Please check';
END IF;
	/*成功*/
END */$$
DELIMITER ;
