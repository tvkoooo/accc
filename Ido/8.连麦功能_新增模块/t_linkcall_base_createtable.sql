CREATE DATABASE  IF NOT EXISTS `rcec_record` /*!40100 DEFAULT CHARACTER SET utf8 */;
USE `rcec_record`;
-- MySQL dump 10.13  Distrib 5.7.9, for Win32 (AMD64)
--
-- Host: localhost    Database: rcec_record
-- ------------------------------------------------------
-- Server version	5.7.11-log
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

/*!40101 DROP TABLE IF EXISTS `rcec_record.t_linkcall_base_userlog`*/;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rcec_record.t_linkcall_base_userlog` (
	`link_serial` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
    `time_apply` datetime NOT NULL DEFAULT '1000-01-01 00:00:00' COMMENT '用户申请时间',
    `user_id` int(11) unsigned NOT NULL  COMMENT '申请人id',
    `singer_id` int(11) unsigned NOT NULL  COMMENT '主播id',	
    `link_success` tinyint(1) unsigned NOT NULL  COMMENT '是否连麦成功',	
    `time_allow_end` datetime NOT NULL DEFAULT '1000-01-01 00:00:00' COMMENT '连麦结束时间',
    `link_time` int(11) unsigned DEFAULT '0' COMMENT '连麦时长',
    `link_first` tinyint(1) unsigned NOT NULL COMMENT '用户是否首次连麦',	
    PRIMARY KEY (`link_serial`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='连麦用户使用日志表';
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 DROP TABLE IF EXISTS `easytab_address`*/;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
