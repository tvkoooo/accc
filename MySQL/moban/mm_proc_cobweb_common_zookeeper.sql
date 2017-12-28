CREATE DATABASE  IF NOT EXISTS `xcbb_cobweb` /*!40100 DEFAULT CHARACTER SET utf8 */;
USE `xcbb_cobweb`;
-- MySQL dump 10.13  Distrib 5.7.9, for Win32 (AMD64)
--
-- Host: localhost    Database: xcbb_cobweb
-- ------------------------------------------------------
/*
error code
	/////////////////
	0 = 核心,
	{
		     -1(-1)未知错误
		      0( 0)成功 
	}
	/////////////////
	0 = 普通,
	{
		20000001(1)插入数据失败
		20000002(2)获取数据失败
		20000003(3)数据更新失败
		20000004(4)删除数据失败
		20000005(5)传入参数错误
	}
*/
/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

/*!50003 DROP PROCEDURE IF EXISTS `p_get_zookeeper_cluster`*/;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE PROCEDURE `p_get_zookeeper_cluster`(
	OUT `_code` int(11) unsigned,
	IN `_cluster_id` int(11) unsigned)
BEGIN
	/*获取zk集群数据*/
	/* 传入
		_code,
		_cluster_id
		*/
	/* 2001000 */
	/* 返回
		错误码 
				 0( 0)成功 
		服务信息
		`cluster_state`,集群状态(0停止 1工作)
		`node_number`,集群规模,节点数量
		`desc`,集群描述
		*/
		
	SELECT 
		`cluster_state`,
		`node_number`,
		`desc`
	FROM xcbb_cobweb.t_server_zookeeper_cluster
	WHERE (`cluster_id` = _cluster_id);

	/*成功*/
	set _code = 0;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

/*!50003 DROP PROCEDURE IF EXISTS `p_set_zookeeper_cluster`*/;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE PROCEDURE `p_set_zookeeper_cluster`(
	OUT `_code` int(11) unsigned,
	IN `_cluster_id` int(11) unsigned,
	IN `_cluster_state` int(11) unsigned,
	IN `_node_number` float,
	IN `_desc` char(64))
BEGIN
	/*设置zk集群数据*/
	/* 传入
		_code,
		_cluster_id,
		_cluster_state,
		_node_number,
		_desc,
		*/
	/* 返回
		错误码 
				 0( 0)成功 
		*/
	INSERT INTO xcbb_cobweb.t_zookeeper_cluster(`cluster_id`,`cluster_state`,`node_number`,`desc`) 
	VALUES                                   (_cluster_id ,_cluster_state ,_node_number ,_desc )
	ON DUPLICATE KEY UPDATE 
		`cluster_id`=_cluster_id,
		`cluster_state`=_cluster_state,
		`node_number`=_node_number,
		`desc`=_desc;

	/*成功*/
	set _code = 0;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

/*!50003 DROP PROCEDURE IF EXISTS `p_get_zookeeper_node`*/;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE PROCEDURE `p_get_zookeeper_node`(
	OUT `_code` int(11) unsigned,
	IN `_unique_id` int(11) unsigned)
BEGIN
	/*获取zk节点数据*/
	/* 传入
		_code,
		_unique_id
		*/
	/* 2001000 */
	/* 返回
		错误码 
				 0( 0)成功 
		服务信息
		`cluster_id`,集群编号
		*/

	SELECT 
		`cluster_id`
	FROM xcbb_cobweb.t_zookeeper_node
	WHERE (`unique_id` = _unique_id);

	/*成功*/
	set _code = 0;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

/*!50003 DROP PROCEDURE IF EXISTS `p_set_zookeeper_node`*/;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE PROCEDURE `p_set_zookeeper_node`(
	OUT `_code` int(11) unsigned,
	IN `_unique_id` int(11) unsigned,
	IN `_cluster_id` int(11) unsigned)
BEGIN
	/*设置zk节点数据*/
	/* 传入
		_code,
		_unique_id,
		_cluster_id
		*/
	/* 返回
		错误码 
				 0( 0)成功 
		*/
	INSERT INTO xcbb_cobweb.t_zookeeper_node(`unique_id`,`cluster_id`) 
	VALUES                                (_unique_id ,_cluster_id )
	ON DUPLICATE KEY UPDATE 
		`unique_id`=_unique_id,
		`cluster_id`=_cluster_id;

	/*成功*/
	set _code = 0;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

/*!50003 DROP PROCEDURE IF EXISTS `p_select_zookeeper_cluster_node_list`*/;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE PROCEDURE `p_select_zookeeper_cluster_node_list`(
	OUT `_code` int(11) unsigned,
	IN `_cluster_id` int(11) unsigned)
BEGIN
	/*获取zk集群所有节点数据*/
	/* 传入
		_code,
		_cluster_id
		*/
	/* 2001000 */
	/* 返回
		错误码 
				 0( 0)成功 
		服务信息
		`unique_id`,集群编号
		`cluster_id`,集群编号
		`node`,ip地址
		`port`,端口地址
		*/

	SELECT 
		`tszn`.`unique_id`,
		`tszn`.`cluster_id`,
		`tsca`.`node`,
		`tsca`.`port`
	FROM xcbb_cobweb.t_zookeeper_node tszn 
	LEFT JOIN xcbb_cobweb.t_config_address tsca
	ON (`tszn`.`unique_id` = `tsca`.`unique_id`)
	WHERE ( tszn.cluster_id = _cluster_id );

	/*成功*/
	set _code = 0;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
