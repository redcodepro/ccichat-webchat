-- MySQL dump 10.13  Distrib 8.0.29, for Win64 (x86_64)
--
-- Host: 192.168.47.145    Database: webchat
-- ------------------------------------------------------
-- Server version	8.0.30-0ubuntu0.22.04.1

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `chat_auth`
--

DROP TABLE IF EXISTS `chat_auth`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `chat_auth` (
  `id` int NOT NULL,
  `password` tinytext CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NOT NULL,
  `session` tinytext CHARACTER SET utf8mb3 COLLATE utf8mb3_bin,
  `restore` tinytext CHARACTER SET utf8mb3 COLLATE utf8mb3_bin,
  PRIMARY KEY (`id`),
  CONSTRAINT `chat_auth_ibfk_1` FOREIGN KEY (`id`) REFERENCES `chat_users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `chat_auth`
--

LOCK TABLES `chat_auth` WRITE;
/*!40000 ALTER TABLE `chat_auth` DISABLE KEYS */;
INSERT INTO `chat_auth` VALUES (1,'4297f44b13955235245b2497399d7a93',NULL,NULL);
/*!40000 ALTER TABLE `chat_auth` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `chat_blacklist`
--

DROP TABLE IF EXISTS `chat_blacklist`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `chat_blacklist` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=69 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `chat_blacklist`
--

LOCK TABLES `chat_blacklist` WRITE;
/*!40000 ALTER TABLE `chat_blacklist` DISABLE KEYS */;
INSERT INTO `chat_blacklist` VALUES (2,'system'),(3,'default'),(4,'webchat'),(5,'console'),(8,'anonymous'),(11,'random'),(13,'server'),(15,'arizona'),(16,'arizonarp'),(17,'banned'),(18,'blast'),(19,'blasthk'),(20,'blasthack'),(21,'password'),(35,'generic'),(37,'hidden'),(38,'chapo'),(41,'moderator'),(42,'moder'),(43,'friend'),(44,'username'),(45,'gtasa'),(46,'login'),(47,'register'),(48,'banip'),(52,'format'),(62,'pidor'),(63,'123123'),(64,'123321'),(65,'piska');
/*!40000 ALTER TABLE `chat_blacklist` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `chat_format`
--

DROP TABLE IF EXISTS `chat_format`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `chat_format` (
  `id` int NOT NULL AUTO_INCREMENT,
  `fmt` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `chat_format`
--

LOCK TABLES `chat_format` WRITE;
/*!40000 ALTER TABLE `chat_format` DISABLE KEYS */;
INSERT INTO `chat_format` VALUES (0,'%s'),(1,'%s{ffffff}: %s'),(2,'%s {f7f488}подключился'),(3,'%s {f7f488}вернулся'),(4,'%s {f7f488}отключился'),(5,'{ff6347}Модератор %s {ff6347}забанил %s{ff6347}. Причина: {ffffff}%s'),(6,'{cc1f00}Модератор %s {cc1f00}забанил %s{cc1f00}. Причина: {ffffff}%s'),(7,'{f7f488}[%s >> %s]{ffffff} %s'),(8,'');
/*!40000 ALTER TABLE `chat_format` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `chat_log`
--

DROP TABLE IF EXISTS `chat_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `chat_log` (
  `id` int NOT NULL AUTO_INCREMENT,
  `time` int NOT NULL,
  `type` int NOT NULL,
  `u0` int NOT NULL,
  `u1` int NOT NULL,
  `text` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `u0` (`u0`),
  KEY `u1` (`u1`),
  KEY `type` (`type`),
  CONSTRAINT `chat_log_ibfk_1` FOREIGN KEY (`u0`) REFERENCES `chat_users` (`id`),
  CONSTRAINT `chat_log_ibfk_2` FOREIGN KEY (`u1`) REFERENCES `chat_users` (`id`),
  CONSTRAINT `chat_log_ibfk_3` FOREIGN KEY (`type`) REFERENCES `chat_format` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `chat_log`
--

LOCK TABLES `chat_log` WRITE;
/*!40000 ALTER TABLE `chat_log` DISABLE KEYS */;
INSERT INTO `chat_log` VALUES (1,0,1,1,0,'test');
/*!40000 ALTER TABLE `chat_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `chat_users`
--

DROP TABLE IF EXISTS `chat_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `chat_users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` tinytext CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `prefix` tinytext CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `status` int NOT NULL DEFAULT '1',
  `authtime` int NOT NULL DEFAULT '0',
  `lastmsg` int NOT NULL DEFAULT '0',
  `ip` tinytext CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=100 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `chat_users`
--

LOCK TABLES `chat_users` WRITE;
/*!40000 ALTER TABLE `chat_users` DISABLE KEYS */;
INSERT INTO `chat_users` VALUES (0,'',NULL,0,0,0,NULL),(1,'admin','{ff0000}',5,0,0,NULL);
/*!40000 ALTER TABLE `chat_users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2022-10-02 16:39:50
