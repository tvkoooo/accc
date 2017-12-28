CREATE DATABASE  IF NOT EXISTS `easydb` /*!40100 DEFAULT CHARACTER SET utf8 */;
USE `easydb`;
-- MySQL dump 10.13  Distrib 5.7.9, for Win32 (AMD64)
--
-- Host: localhost    Database: easydb
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

/*!40101 DROP TABLE IF EXISTS `easytab`*/;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `t_userinfo` (
    `user_id` int(11) unsigned NOT NULL AUTO_INCREMENT comment '用户id',
    `user_name` char(20) NOT NULL  comment '用户名',
    `user_password` char(20) NOT NULL  comment '用户密码',
    `user_money` int(11) NOT NULL DEFAULT '0' comment '用户钱',
	`user_sun` int(20) NOT NULL DEFAULT '0' comment '用户币',
	`user_level` int(20) NOT NULL DEFAULT '0' comment '用户等级',
	
    PRIMARY KEY (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=52010000 DEFAULT CHARSET=utf8 comment='用户配置数据表';
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 DROP TABLE IF EXISTS `easytab_address`*/;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;

