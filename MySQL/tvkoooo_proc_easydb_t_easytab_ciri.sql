CREATE DATABASE  IF NOT EXISTS `easydb` /*!40100 DEFAULT CHARACTER SET utf8 */;
USE `easydb`;
/*
SQLyog Ultimate v11.27 (32 bit)
MySQL - 5.5.44-0ubuntu0.12.04.1-log : Database - easydb
*********************************************************************
*/


/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
CREATE DATABASE /*!32312 IF NOT EXISTS*/`easydb` /*!40100 DEFAULT CHARACTER SET utf8 */;

USE `easydb`;

/* Procedure structure for procedure `p_chat_easydb_t_easytab_ciri` */

/*!50003 DROP PROCEDURE IF EXISTS  `p_chat_easydb_t_easytab_ciri` */;

DELIMITER $$

/*!50003 CREATE  PROCEDURE `p_chat_easydb_t_easytab_ciri`(
	IN `_ciri` INT)
BEGIN
		/*获取数据信息*/
		/* 传入
			_user_name,

			*/
		/* 2001000 */
		/* 返回
			错误码 
					 0( 0)成功 
			服务信息

		*/
	
select *,
lastdate-last_2date 
as 次日访问 
from t_easytab 
where 
(lastdate-last_2date='1');
	/*成功*/

    END */$$
DELIMITER ;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
