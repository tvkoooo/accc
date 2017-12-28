CREATE DATABASE  IF NOT EXISTS `cms_manager` /*!40100 DEFAULT CHARACTER SET utf8 */;
USE `cms_manager`;
-- MySQL dump 10.13  Distrib 5.7.9, for Win32 (AMD64)
--
-- Host: localhost    Database: cms_manager
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

LOCK TABLES `cms_manager`.`t_robot_talking_base` WRITE;
/*!40000 ALTER TABLE `cms_manager`.`t_robot_talking_base` DISABLE KEYS */;
INSERT INTO `cms_manager`.`t_robot_talking_base` VALUES 
(17086,1,'127.0.0.1'),
(10033,1,'127.0.0.1'),
(11221,1,'127.0.0.1'),
(10129,2,'127.0.0.1'),
(10456,1,'127.0.0.1'),
(10023,1,'127.0.0.1'),
(10055,3,'127.0.0.1'),
(10127,1,'127.0.0.1'),
(10012,3,'127.0.0.1'),
(10027,1,'127.0.0.1');
/*!40000 ALTER TABLE `cms_manager`.`t_robot_talking_base` ENABLE KEYS */;
UNLOCK TABLES;

/*!40101 DROP TABLE IF EXISTS `easytab_address`*/;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;