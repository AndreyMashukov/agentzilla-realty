-- MySQL dump 10.16  Distrib 10.1.21-MariaDB, for Linux (x86_64)
--
-- Host: 192.168.1.155    Database: 192.168.1.155
-- ------------------------------------------------------
-- Server version	10.1.21-MariaDB

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
-- Table structure for table `addresses`
--

DROP TABLE IF EXISTS `addresses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `addresses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `city` char(100) NOT NULL,
  `hash` char(40) NOT NULL,
  `address` char(200) NOT NULL,
  `phone` char(15) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `search` (`city`),
  KEY `contact` (`phone`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `addresses`
--

LOCK TABLES `addresses` WRITE;
/*!40000 ALTER TABLE `addresses` DISABLE KEYS */;
INSERT INTO `addresses` VALUES (1,'Иркутск','','бул. Рябикова 2а','89025111675'),(2,'Иркутск','','Постышева б-р, 29','89027638001'),(3,'Иркутск','','Марково','89246067362'),(4,'Иркутск','','Сибирских Партизан, 11а','89627779797'),(5,'Иркутск','','Ярославского, 382','89025669945'),(6,'Иркутск','','Приморский мкр, 10','89086664440'),(7,'Чита','','Журавлева, 68','83022367881'),(8,'Чита','','Советская, 17б.','83022367881'),(9,'Чита','','Шилова, 92','83022367881'),(10,'Иркутск','','Советская, 96','89086581039'),(11,'Иркутск','','Шайдурова, 34','89526191914'),(12,'Иркутск','','ул Шайдурова, 34','89526191914'),(13,'Иркутск','','Аэрофлотская, 34','89526191914'),(14,'Иркутск','','Депутатская, 56','89526191914'),(15,'Иркутск','','Лермонтова, 54','89526191914'),(16,'Иркутск','','Лермонтова, 154','89526191914'),(17,'Иркутск','','Невского, 89','89526191914');
/*!40000 ALTER TABLE `addresses` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2017-06-11 11:24:30
