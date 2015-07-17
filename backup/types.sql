-- MySQL dump 10.10
--
-- Host: localhost    Database: travma
-- ------------------------------------------------------
-- Server version	5.5.25

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES cp1251 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `rb_trauma_types`
--

DROP TABLE IF EXISTS `rb_trauma_types`;
CREATE TABLE `rb_trauma_types` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` int(11) DEFAULT NULL,
  `name` varchar(64) NOT NULL DEFAULT '',
  `atwork` int(11) NOT NULL DEFAULT '0',
  `f57_col_adult` varchar(16) NOT NULL DEFAULT '',
  `f57_col_child` varchar(16) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8 COMMENT='Типы травмы (бытовая/производств';

--
-- Dumping data for table `rb_trauma_types`
--


/*!40000 ALTER TABLE `rb_trauma_types` DISABLE KEYS */;
LOCK TABLES `rb_trauma_types` WRITE;
INSERT INTO `rb_trauma_types` VALUES (1,5,'Связанные с производством:транспортные',1,'2,3,11,20',''),(2,3,'Связанные с производством:транспортные автодорожные',1,'2,3,11,20',''),(3,6,'Связанные с производством:прочие',1,'4,11,20','18,19,20'),(4,7,'Связанные с производством:транспортные',1,'2,11,20','14,15,19,20'),(5,9,'Не связанные с производством:уличные',1,'6,11,20','13,19,20'),(6,1,'Связанные с производством:в промышленности',1,'0,11,20','12,19,20'),(7,10,'Не связанные с производством:транспортные',0,'7,11,20','14,15,19,20'),(8,4,'Связанные с производством:в сельском хозяйстве',0,'1,11,20','14,15,19,20'),(9,11,'Не связанные с производством:транспортные автодорожные',0,'7,8,11,20','15,19,20'),(10,12,'Не связанные с производством:спортивные',0,'9,11','17,19,20'),(11,8,'Не связанные с производством бытовые',0,'5,11,20','12,19,20'),(12,13,'Полученные в результате террористических действий',0,'','');
UNLOCK TABLES;
/*!40000 ALTER TABLE `rb_trauma_types` ENABLE KEYS */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

