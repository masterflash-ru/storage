-- MySQL dump 10.13  Distrib 5.6.45, for FreeBSD12.0 (i386)
--
-- Host: localhost    Database: simba4
-- ------------------------------------------------------
-- Server version	5.6.45-log

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

--
-- Table structure for table `storage`
--

DROP TABLE IF EXISTS `storage`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `storage` (
  `id` int(11) unsigned NOT NULL,
  `razdel` char(50) NOT NULL COMMENT 'раздел, например, news',
  `todelete` int(11) DEFAULT '0' COMMENT 'флаг что нужно удалить эти фото',
  `file_array` text COMMENT 'структура serialize массива имен файлов',
  `version` float(9,1) DEFAULT NULL COMMENT 'версия хранилища',
  PRIMARY KEY (`id`,`razdel`),
  KEY `todelete` (`todelete`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Хранилище файлов';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `storage`
--

LOCK TABLES `storage` WRITE;
/*!40000 ALTER TABLE `storage` DISABLE KEYS */;
/*!40000 ALTER TABLE `storage` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `storage_gallery`
--

DROP TABLE IF EXISTS `storage_gallery`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `storage_gallery` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `razdel` char(50) NOT NULL,
  `gallery_number` int(11) NOT NULL COMMENT 'номер галереи, начиная с 0',
  `date_public` datetime DEFAULT NULL COMMENT 'дата публикации',
  `public` int(11) DEFAULT NULL COMMENT 'флаг публикации',
  `todelete` int(11) DEFAULT NULL COMMENT 'флаг удаления',
  `poz` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`,`razdel`),
  KEY `gallery_number` (`gallery_number`),
  KEY `todelete` (`todelete`),
  KEY `date_public` (`date_public`),
  KEY `public` (`public`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='галерея в хранилище';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `storage_gallery`
--

LOCK TABLES `storage_gallery` WRITE;
/*!40000 ALTER TABLE `storage_gallery` DISABLE KEYS */;
/*!40000 ALTER TABLE `storage_gallery` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_CREATE_USER' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50003 TRIGGER `storage_gallery_before_del_tr` BEFORE DELETE ON `storage_gallery`
  FOR EACH ROW
BEGIN
update storage set todelete=1 where razdel="storage_gallery" and id=OLD.id;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2019-09-17 14:07:12
