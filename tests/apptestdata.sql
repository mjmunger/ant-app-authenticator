-- MySQL dump 10.13  Distrib 5.7.17, for Linux (x86_64)
--
-- Host: localhost    Database: phpant
-- ------------------------------------------------------
-- Server version	5.7.17

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
-- Table structure for table `Version`
--

DROP TABLE IF EXISTS `Version`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Version` (
  `VersionId` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`VersionId`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Version`
--

LOCK TABLES `Version` WRITE;
/*!40000 ALTER TABLE `Version` DISABLE KEYS */;
/*!40000 ALTER TABLE `Version` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users_roles`
--

DROP TABLE IF EXISTS `users_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users_roles` (
  `users_roles_id` int(11) NOT NULL AUTO_INCREMENT,
  `users_roles_title` varchar(45) DEFAULT NULL,
  `users_roles_role` varchar(1) DEFAULT 'U' COMMENT 'A - Administrator\nU - Standard User',
  PRIMARY KEY (`users_roles_id`),
  UNIQUE KEY `users_roles_role_UNIQUE` (`users_roles_role`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users_roles`
--

LOCK TABLES `users_roles` WRITE;
/*!40000 ALTER TABLE `users_roles` DISABLE KEYS */;
INSERT INTO `users_roles` VALUES (1,'Admin','A'),(2,'User','U');
/*!40000 ALTER TABLE `users_roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `acls`
--

DROP TABLE IF EXISTS `acls`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `acls` (
  `acls_id` int(11) NOT NULL AUTO_INCREMENT,
  `users_roles_id` int(11) NOT NULL,
  `acls_event` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`acls_id`),
  KEY `fk_acls_users_roles1_idx` (`users_roles_id`),
  CONSTRAINT `fk_acls_users_roles1` FOREIGN KEY (`users_roles_id`) REFERENCES `users_roles` (`users_roles_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `acls`
--

LOCK TABLES `acls` WRITE;
/*!40000 ALTER TABLE `acls` DISABLE KEYS */;
/*!40000 ALTER TABLE `acls` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `announce`
--

DROP TABLE IF EXISTS `announce`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `announce` (
  `announce_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `announce_created` datetime DEFAULT NULL,
  `announce_api_key` varchar(41) DEFAULT NULL,
  `announce_api_key_verified` datetime DEFAULT NULL,
  `announce_api_key_accepted` varchar(1) DEFAULT NULL COMMENT 'Y - accepted, N - does not exist, D - disabled, NULL - not yet processed',
  `announce_signature_verified` datetime DEFAULT NULL,
  `announce_signature_accepted` varchar(1) DEFAULT NULL COMMENT 'Y - accepted, N - not accepted, NULL - not yet processed',
  `announce_sandbox` varchar(1) DEFAULT NULL COMMENT 'Y - sandbox announce, otherwise - regular announce',
  `announce_validated` datetime DEFAULT NULL,
  `announce_status` text CHARACTER SET latin1 COMMENT 'OK - announce accepted for processing, otherwise - rejection reason',
  `announce_processed` datetime DEFAULT NULL,
  `announce_accepted` varchar(1) DEFAULT NULL COMMENT 'Y - accepted, N - rejected, NULL - not yet processed',
  `announce_notified` datetime DEFAULT NULL,
  `announce_sponsorship_id` int(11) DEFAULT NULL,
  `announce_type` varchar(255) CHARACTER SET latin1 DEFAULT NULL,
  `announce_double_sided` tinyint(1) NOT NULL DEFAULT '0',
  `announce_material` text,
  `announce_width` decimal(5,2) unsigned DEFAULT NULL,
  `announce_height` decimal(5,2) unsigned DEFAULT NULL,
  `announce_bleed` tinyint(3) unsigned DEFAULT NULL,
  `announce_dpi` smallint(6) unsigned DEFAULT NULL,
  `announce_filetypes` text CHARACTER SET latin1,
  `announce_callback` text CHARACTER SET latin1,
  `announce_billing_account` int(11) DEFAULT NULL,
  `announce_paid` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `announce_submit` tinyint(1) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`announce_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Submitted requests';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `announce`
--

LOCK TABLES `announce` WRITE;
/*!40000 ALTER TABLE `announce` DISABLE KEYS */;
/*!40000 ALTER TABLE `announce` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `adspace`
--

DROP TABLE IF EXISTS `adspace`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `adspace` (
  `adspace_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `adspace_announce_id` bigint(20) unsigned DEFAULT NULL,
  `adspace_adcode` varchar(255) DEFAULT NULL,
  `adspace_name` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`adspace_id`),
  KEY `fk_adspace_announce_idx` (`adspace_announce_id`),
  CONSTRAINT `fk_adspace_announce` FOREIGN KEY (`adspace_announce_id`) REFERENCES `announce` (`announce_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `adspace`
--

LOCK TABLES `adspace` WRITE;
/*!40000 ALTER TABLE `adspace` DISABLE KEYS */;
/*!40000 ALTER TABLE `adspace` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `company`
--

DROP TABLE IF EXISTS `company`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `company` (
  `company_id` int(11) NOT NULL AUTO_INCREMENT,
  `company_name` varchar(255) DEFAULT NULL,
  `company_folder` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`company_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `company`
--

LOCK TABLES `company` WRITE;
/*!40000 ALTER TABLE `company` DISABLE KEYS */;
INSERT INTO `company` VALUES (1,'test','test'),(2,'amc','amc');
/*!40000 ALTER TABLE `company` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `api_key`
--

DROP TABLE IF EXISTS `api_key`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `api_key` (
  `api_key_id` int(11) NOT NULL AUTO_INCREMENT,
  `api_key_created` datetime DEFAULT NULL,
  `api_key_key` varchar(41) DEFAULT NULL,
  `api_key_info` varchar(255) DEFAULT NULL,
  `api_key_enabled` varchar(1) DEFAULT 'Y' COMMENT 'Y to enable, N to disable',
  `api_key_sandbox` varchar(1) DEFAULT 'N' COMMENT 'Y for sandbox key, N for regular key',
  `api_key_public_key` varchar(44) DEFAULT NULL,
  `api_key_company_id` int(11) NOT NULL,
  PRIMARY KEY (`api_key_id`),
  KEY `fk_api_key_company_idx` (`api_key_company_id`),
  CONSTRAINT `fk_api_key_company` FOREIGN KEY (`api_key_company_id`) REFERENCES `company` (`company_id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `api_key`
--

LOCK TABLES `api_key` WRITE;
/*!40000 ALTER TABLE `api_key` DISABLE KEYS */;
INSERT INTO `api_key` VALUES (1,'2016-12-23 17:32:17','qswdekttnqnzgdvmxsnayxbbywrpkyfyrcbvnvnzh','Test','Y','Y','vcJYpCwenR579OHOnURuwzuVvw1x98CIai5Wngo5Qbs=',1),(2,'2017-01-10 21:07:29','bgywrugpsvbgzuesbhctdxtwmykqsgatrzqftxuks','amc uat','N','Y','zzjQ/1x9iK05BaY5N6RuvWmWr4t4z3XBD+xsUaaGsx8=',2),(3,'2017-01-10 21:08:43','wtsrxhsfmhmgafuysgryghqfxvzvqknkbdzznreyz','amc production','Y','N','OmSOtjuTZkYYEZIMJHXQl1v1Q+MxjYrHTPoNEtsQvnM=',2);
/*!40000 ALTER TABLE `api_key` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `api_keys`
--

DROP TABLE IF EXISTS `api_keys`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `api_keys` (
  `api_keys_id` int(11) NOT NULL AUTO_INCREMENT,
  `api_keys_key` varchar(41) DEFAULT NULL,
  `api_keys_info` varchar(255) DEFAULT NULL,
  `api_keys_enabled` varchar(1) DEFAULT 'Y' COMMENT 'Y to enable, N to disable.',
  `api_keys_generated` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`api_keys_id`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `api_keys`
--

LOCK TABLES `api_keys` WRITE;
/*!40000 ALTER TABLE `api_keys` DISABLE KEYS */;
INSERT INTO `api_keys` VALUES (9,'dpzavyngfhgzbawbuhsxvbrgtshncmxgywhtuvzac','asdfasdf','N','2016-02-29 04:20:10'),(10,'tqvrvqtupfwdnmruhvngfzcerpwtxfmrwfervykhe','Test key','N','2016-02-29 04:34:59'),(11,'refehfrywbwadpcnhrhvntrtwqhsptmcyshgyyeva','asdf','N','2016-02-29 04:59:09'),(12,'bxaseqebkeuqahagfvvwtwzfqfzyqhxseygkppnxt','1234','N','2016-02-29 05:00:48'),(13,'hfzfdghfxzxvnxpdnycexynfunnntwdaccffmrpgq','1234','N','2016-02-29 05:01:06'),(14,'fctczwgwnbsgxevvdunmqtwwtxwsxxvcznfzhqvvr','asdf123','N','2016-02-29 05:01:41'),(15,'kvthubxvqwpmsucceyzctctbsnrfmtwwnqdfsaaew','asdf123','N','2016-02-29 05:02:01'),(16,'vhmrrrqzpnhsyacfuaayfksrvqtsvwarenfvcvvrg','Dev','Y','2016-02-29 05:02:51'),(17,'zpfdymmfywzepfzugrzdrxvmcacddwgdkpggztpxq','Foo App','N','2016-02-29 05:03:18'),(18,'cgmckutavuhbdqhmkwgekqhrdxhqyadagqctgwtcq','Bar App','Y','2016-02-29 05:11:01'),(19,'mgsekgfttwtanbqfeqthcybsrzrxqbdzhweucmmvf','Some User','Y','2016-02-29 05:14:47'),(20,'gentxyezqtxuuafgkhhmrdawgmstarvgwfaueeuuy','Something else','Y','2016-03-01 02:17:32');
/*!40000 ALTER TABLE `api_keys` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `users_id` int(11) NOT NULL AUTO_INCREMENT,
  `users_email` varchar(255) DEFAULT NULL,
  `users_password` varchar(60) DEFAULT NULL,
  `users_first` varchar(45) DEFAULT NULL,
  `users_last` varchar(45) DEFAULT NULL,
  `users_setup` varchar(1) DEFAULT 'N',
  `users_nonce` varchar(32) DEFAULT NULL,
  `users_token` varchar(65) DEFAULT NULL,
  `users_active` varchar(1) DEFAULT 'Y',
  `users_last_login` int(11) DEFAULT NULL,
  `users_mobile_token` varchar(8) DEFAULT NULL COMMENT 'The token for mobile. This allows a user to log in via the browser AND their mobile phone.',
  `users_public_key` text COMMENT 'Holds the RSA or PGP public key for hashing.',
  `users_owner_id` int(11) DEFAULT NULL,
  `users_timezone` varchar(100) DEFAULT NULL,
  `users_roles_id` int(11) NOT NULL,
  `users_guid` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`users_id`,`users_roles_id`),
  KEY `fk_users_users_roles_idx` (`users_roles_id`),
  CONSTRAINT `fk_users_1` FOREIGN KEY (`users_roles_id`) REFERENCES `users_roles` (`users_roles_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'michael@highpoweredhelp.com','sha256:1000:ZAI+EiDE+NMdXPAspmpcDuiiNPOc5H72:f/NnjE4uD0G+HkN','Michael','Munger','Y',NULL,'26bce1a2','Y',NULL,NULL,NULL,NULL,NULL,1,NULL),(2,'itatartrate@precompounding.co.uk','$2y$10$eM4iUk/NqBf3Gjd5m0GetevniEKZuui02ml6oW0rs/B/s4A/vkvwC','Susanna','Whtie','N',NULL,NULL,'Y',NULL,NULL,NULL,NULL,NULL,2,NULL),(3,'tephramancy@gatewise.co.uk','$2y$10$TBuc/1gDoi3ynm6fZuGJTe0Xc7sgZO0N4TLPykdpueUcCekUA0Ofy','Augustus','Stilwagen','N',NULL,NULL,'Y',NULL,NULL,NULL,NULL,NULL,2,NULL),(4,'unsanctimoniousness@coenoecic.edu','$2y$10$tPEKFJo6YUXslXqFex/NzuNKwXVjPJKNE7zVdcm2eQC9R1nBOD2j2','Jesenia','Scanio','N',NULL,NULL,'Y',NULL,NULL,NULL,NULL,NULL,2,NULL),(5,'extranatural@possumwood.net','$2y$10$Sw1JUKy1LsSXJI12GlqtB.LhySi9WNPgRvhB8y0p1CAco2PL1ZMbe','Geoffrey','Orlander','N',NULL,NULL,'Y',NULL,NULL,NULL,NULL,NULL,2,NULL),(6,'ureteral@amorphy.edu','$2y$10$J5aXat5qPoP6Y6O.oXkLn.HsLLPB0N9ChDKk.0mNTZS/zRgNDjiG.','Irvin','Lizaola','N',NULL,NULL,'Y',NULL,NULL,NULL,NULL,NULL,2,NULL),(7,'Santti@indologist.edu','$2y$10$UJJ3oIXqgMx2ZkO6TRay3eLEWXT7J13A00lP4Wnlmi3d2xPiXd1CO','Michele','Santti',NULL,NULL,NULL,'Y',NULL,NULL,NULL,NULL,NULL,2,NULL);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `email_log`
--

DROP TABLE IF EXISTS `email_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `email_log` (
  `email_log_id` int(11) NOT NULL AUTO_INCREMENT,
  `email_log_to` varchar(255) DEFAULT NULL COMMENT 'The address to whom this person was sent. This is historical since emails can change.',
  `email_log_from` varchar(255) DEFAULT NULL,
  `email_log_subject` varchar(255) DEFAULT NULL,
  `email_log_body` text,
  `email_log_headers` text,
  `email_log_disposition` varchar(255) DEFAULT NULL,
  `users_id` int(11) NOT NULL COMMENT 'The user to whom this email was sent.',
  `users_roles_id` int(11) NOT NULL COMMENT 'The role of the person to whom this was sent.\n',
  `email_log_timestamp_sent` int(11) DEFAULT NULL,
  `email_log_timestamp_sent_local` varchar(45) DEFAULT NULL COMMENT 'The local time (for the user) that the message was sent.\n',
  PRIMARY KEY (`email_log_id`,`users_id`,`users_roles_id`),
  KEY `fk_email_log_users1_idx` (`users_id`,`users_roles_id`),
  CONSTRAINT `fk_email_log_1` FOREIGN KEY (`users_id`) REFERENCES `users` (`users_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `email_log`
--

LOCK TABLES `email_log` WRITE;
/*!40000 ALTER TABLE `email_log` DISABLE KEYS */;
/*!40000 ALTER TABLE `email_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `file`
--

DROP TABLE IF EXISTS `file`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `file` (
  `file_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `file_announce_id` bigint(20) unsigned DEFAULT NULL,
  `file_validated` datetime DEFAULT NULL,
  `file_status` text COMMENT 'check on request submission: NULL - ok, otherwise - rejection reason',
  `file_downloaded` datetime DEFAULT NULL COMMENT 'file downloaded, hash verified successfully ',
  `file_download_failed` datetime DEFAULT NULL COMMENT 'download file attempt number reached system''s maximum',
  `file_download_attempts` tinyint(4) DEFAULT '0',
  `file_preflight_performed` datetime DEFAULT NULL,
  `file_preflight_status` text COMMENT 'NULL - preflight passed, otherwise - list of rejection reasons',
  `file_done` datetime DEFAULT NULL,
  `file_art_id` int(10) unsigned DEFAULT NULL,
  `file_url` text,
  `file_path` text,
  `file_hash` varchar(64) DEFAULT NULL,
  PRIMARY KEY (`file_id`),
  KEY `fk_file_announce_idx` (`file_announce_id`),
  CONSTRAINT `fk_file_announce` FOREIGN KEY (`file_announce_id`) REFERENCES `announce` (`announce_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Files submitted in announcements';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `file`
--

LOCK TABLES `file` WRITE;
/*!40000 ALTER TABLE `file` DISABLE KEYS */;
/*!40000 ALTER TABLE `file` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `logs`
--

DROP TABLE IF EXISTS `logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `logs` (
  `logs_id` int(11) NOT NULL AUTO_INCREMENT,
  `logs_timestamp` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `logs_component` varchar(45) DEFAULT NULL,
  `logs_message` text,
  PRIMARY KEY (`logs_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `logs`
--

LOCK TABLES `logs` WRITE;
/*!40000 ALTER TABLE `logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `settings`
--

DROP TABLE IF EXISTS `settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `settings` (
  `settings_id` int(11) NOT NULL AUTO_INCREMENT,
  `settings_key` varchar(255) DEFAULT NULL,
  `settings_value` text,
  PRIMARY KEY (`settings_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `settings`
--

LOCK TABLES `settings` WRITE;
/*!40000 ALTER TABLE `settings` DISABLE KEYS */;
INSERT INTO `settings` VALUES (1,'enabledAppsList','{\"+Core App Manager\":\"\\/home\\/michael\\/php\\/php-ant\\/includes\\/apps\\/phpant-app-manager\\/app.php\",\"Config Management\":\"\\/home\\/michael\\/php-ant\\/includes\\/apps\\/ant-app-configs\\/app.php\",\"Default Grammar\":\"\\/home\\/michael\\/php\\/php-ant\\/includes\\/apps\\/ant-app-default\\/app.php\",\"PHPAnt Authenticator\":\"\\/home\\/michael\\/php\\/php-ant\\/includes\\/apps\\/ant-app-authenticator\\/app.php\"}'),(2,'signing-key','/home/michael/private.key'),(3,'BlacklistDisabled','1');
/*!40000 ALTER TABLE `settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_tokens`
--

DROP TABLE IF EXISTS `user_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_tokens` (
  `user_tokens_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_tokens_token` varchar(100) DEFAULT NULL,
  `user_tokens_expiry` timestamp NULL DEFAULT NULL,
  `user_tokens_user_agent` varchar(45) DEFAULT NULL,
  `users_id` int(11) NOT NULL,
  `users_roles_id` int(11) NOT NULL,
  PRIMARY KEY (`user_tokens_id`,`users_id`,`users_roles_id`),
  KEY `fk_user_tokens_users1_idx` (`users_id`,`users_roles_id`),
  CONSTRAINT `fk_user_tokens_1` FOREIGN KEY (`users_id`) REFERENCES `users` (`users_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=110 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_tokens`
--

LOCK TABLES `user_tokens` WRITE;
/*!40000 ALTER TABLE `user_tokens` DISABLE KEYS */;
INSERT INTO `user_tokens` VALUES (92,'463f4b5ff3885ebc0d33ee2c5ec732d2af69f6152cefd4becbffb7bf74cd9ac8','2016-10-05 09:58:59',NULL,1,1),(93,'d6cbec51334a8054a1ef46508bf92badce119bd7fb6441bcb434ebc91f212f9d','2016-10-05 09:58:59',NULL,2,2),(94,'d48c3659bdf66e2eccce42f4111c95e8505ed3da2706c740890f62daf3f2a213','2016-10-05 09:58:59',NULL,3,2),(95,'309e228a3c7d252f0939d8d0517d04d9b9f874bab4eec4c4218f952edd4d85d9','2016-10-05 09:58:59',NULL,4,2),(96,'e1261474304406f7a14844227fe4c74261544c2427b5b765ce6225f076879827','2016-10-05 09:58:59',NULL,5,2),(97,'97577059f6d16ea6503739151273b18934916c74873a3f020e749e2c25892cb6','2016-10-05 09:58:59',NULL,6,2),(98,'70800c40b939a576f08334ec47d5976a0bd9a515003518df39f3f0ec175d4279','2016-10-14 09:58:59',NULL,1,1),(99,'0423809fc5f5785af5c08a6fbbd045cc8f883ecded80bd32e522b1b14746ebb9','2016-10-14 09:58:59',NULL,2,2),(100,'78522eca2047c0cc52b6e2d43659df0456a6caadcd907b9c60ae1888aec530e7','2016-10-14 09:58:59',NULL,3,2),(101,'23056492adba4d625e1d9f7f4595e0c045048f8bc9038defe3a786187e402f79','2016-10-14 09:58:59',NULL,4,2),(102,'1b1d22e12520d99b55ca22f4ced7e0e597d0afcaccb212f50e3534b8cd3da52c','2016-10-14 09:58:59',NULL,5,2),(103,'b371e794156036937d462c35f1acf6bfa02de92c57d91d171dec5a04485c366e','2016-10-14 09:58:59',NULL,6,2),(104,'839a6775802a56108d226217b82689f77a3143c5cfae7589840bffb1807790c3','2016-11-03 09:58:59',NULL,1,1),(105,'3fe0e0ebf84b38e8e22b5e44e87cd217d7e515ecaa1a9df90dc88134994e5320','2016-11-03 09:58:59',NULL,2,2),(106,'e447d662ebbec1fdadc7fd919efea7ff21cc0db1d1f8ae010be80c8817a29d0e','2016-11-03 09:58:59',NULL,3,2),(107,'8bfbdec6c9bcf8f0c48235a1b20e0651b6f526e4fb8e117234b6ad859c3ef4a2','2016-11-03 09:58:59',NULL,4,2),(108,'4b31d0f3514015dc6e7bc92f02e4aa716804040a3598af6a002c052865c69e32','2016-11-03 09:58:59',NULL,5,2),(109,'1bc8c2a4d379fd6e16a53d7f8dceb9001fe9bb744759a66b98f61398abd3fcb3','2016-11-03 09:58:59',NULL,6,2);
/*!40000 ALTER TABLE `user_tokens` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2017-05-18 14:50:57
