-- MySQL dump 10.13  Distrib 5.7.21, for Linux (x86_64)
--
-- Host: localhost    Database: phpant
-- ------------------------------------------------------
-- Server version	5.7.21

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
  `api_keys_generated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`api_keys_id`)
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `api_keys`
--

LOCK TABLES `api_keys` WRITE;
/*!40000 ALTER TABLE `api_keys` DISABLE KEYS */;
INSERT INTO `api_keys` VALUES (21,'vhmrrrqzpnhsyacfuaayfksrvqtsvwarenfvcvvrg','test','Y','2018-02-23 05:17:52'),(22,'vhmrrrqzpnhsyacfuaayfksrvqtsvwarenfvcvvrg','test2','Y','2018-02-23 05:17:52'),(23,'zpfdymmfywzepfzugrzdrxvmcacddwgdkpggztpxq','test3','N','2018-02-23 05:17:52'),(24,'zpfdymmfywzepfzugrzdrxvmcacddwgdkpggztpxq','test4','N','2018-02-23 05:17:52');
/*!40000 ALTER TABLE `api_keys` ENABLE KEYS */;
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
  KEY `fk_email_log_users1_idx` (`users_id`,`users_roles_id`)
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
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `settings`
--

LOCK TABLES `settings` WRITE;
/*!40000 ALTER TABLE `settings` DISABLE KEYS */;
/*!40000 ALTER TABLE `settings` ENABLE KEYS */;
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
  UNIQUE KEY `users_roles_title_UNIQUE` (`users_roles_title`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users_roles`
--

LOCK TABLES `users_roles` WRITE;
/*!40000 ALTER TABLE `users_roles` DISABLE KEYS */;
INSERT INTO `users_roles` VALUES (1,'Default','D');
/*!40000 ALTER TABLE `users_roles` ENABLE KEYS */;
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
  `users_token` varchar(8) DEFAULT NULL,
  `users_active` varchar(1) DEFAULT 'Y',
  `users_last_login` datetime DEFAULT NULL,
  `users_public_key` text COMMENT 'Holds the RSA or PGP public key for hashing.',
  `users_owner_id` int(11) DEFAULT NULL,
  `users_timezone` varchar(100) DEFAULT NULL,
  `users_roles_id` int(11) NOT NULL,
  `users_guid` varchar(45) DEFAULT NULL COMMENT 'Used to hold the hex''d object GUID so we do not have to rely on SMTP addresses for authentication.\n',
  PRIMARY KEY (`users_id`,`users_roles_id`),
  KEY `fk_users_users_roles_idx` (`users_roles_id`),
  CONSTRAINT `fk_users_users_roles` FOREIGN KEY (`users_roles_id`) REFERENCES `users_roles` (`users_roles_id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'itatartrate@precompounding.co.uk','$2y$10$06TMLXQUAbGj5iz.DWnQU.ftNzGtD8E8np7OPPd.rAjNclMfOos8a','Susanna','Whtie','N',NULL,NULL,'Y',NULL,NULL,NULL,NULL,1,NULL),(2,'tephramancy@gatewise.co.uk','$2y$10$9It7D/iV7UQ3yZRWsgKfpOnkUaw868EQJ34pCmNyOqD0u6f34iyuG','Augustus','Stilwagen','N',NULL,NULL,'Y',NULL,NULL,NULL,NULL,1,NULL),(3,'unsanctimoniousness@coenoecic.edu','$2y$10$0xppt098PVs1sOILkLx85et31M5254Stc4SDSfmQj20QrHOMPE2uC','Jesenia','Scanio','N',NULL,NULL,'Y',NULL,NULL,NULL,NULL,1,NULL),(4,'extranatural@possumwood.net','$2y$10$qWJQ2vnP57D.S5JkIC4/gOuf4yE.hKs1XLM8lZy06vRd72Lo9AO5W','Geoffrey','Orlander','N',NULL,NULL,'Y',NULL,NULL,NULL,NULL,1,NULL),(5,'ureteral@amorphy.edu','$2y$10$RUTlEpeURV4zQi/rscDs5uXAACYdtsBoXtl43jLOc3b8A7CZH1FEW','Irvin','Lizaola','N',NULL,NULL,'Y',NULL,NULL,NULL,NULL,1,NULL);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
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
  CONSTRAINT `fk_user_tokens_users1` FOREIGN KEY (`users_id`, `users_roles_id`) REFERENCES `users` (`users_id`, `users_roles_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=73 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_tokens`
--

LOCK TABLES `user_tokens` WRITE;
/*!40000 ALTER TABLE `user_tokens` DISABLE KEYS */;
INSERT INTO `user_tokens` VALUES (55,'e956d0a4077e16ffe028687d1415d2d06231bf936ecbfcbb1c8f128dadbead60','2018-02-24 10:12:33',NULL,1,1),(56,'d0d40fd2b8e401d7a9338433fc3ca59410588938b04a1632510e9c4d54338433','2018-02-24 10:12:33',NULL,2,1),(57,'b691182985b7b46103568542ca5920c76c65ad59349b9027b7a841b38f700bd5','2018-02-24 10:12:33',NULL,3,1),(58,'dfe7b1bd6ac200bb590924740a26aa271f2963dd78f25bdde4a8cb9e7c18511b','2018-02-24 10:12:33',NULL,4,1),(59,'cbc2c938edb24b7fb188cc972a505197b5a41c0fe827cbda7331b4f66a6cde4e','2018-02-24 10:12:33',NULL,5,1),(61,'449d2dd54c16d64c13d5e1090176dc992aee381b20c596ba66a1243a4651b80e','2018-03-05 10:12:33',NULL,1,1),(62,'96dc690f41694897122706abd2d4964f043dfbf901ac32d9cba5a78b0d3cfd5e','2018-03-05 10:12:33',NULL,2,1),(63,'1d012d8e0dd4d159f372493b8477977456e02338d3edd13765d3401bb3592a31','2018-03-05 10:12:33',NULL,3,1),(64,'88fec34631225398b919b2739268268a3da39890b0168c1c7d9d28373fab274a','2018-03-05 10:12:33',NULL,4,1),(65,'854f088483e6dd02f7faab31df5e21ec964f3a34a11422d17ae7335b92f91ccd','2018-03-05 10:12:33',NULL,5,1),(67,'bdf3a72cb1c7ab622d25e559edc39de5bdece748e660a95008250ad85ca6bcc0','2018-03-25 09:12:33',NULL,1,1),(68,'6bdb14b8c03be81de931a0fcb512829a87e2c6763046b6bb3dd0aeeb609bcad8','2018-03-25 09:12:33',NULL,2,1),(69,'704cfcf3b8228a7daead861974ce9442270b7306178e10af1cdd57f2f95482ba','2018-03-25 09:12:33',NULL,3,1),(70,'b15c03d6d41e053a0c78d6f77430e6002871cc80d1ccb2ddabe240305757f39e','2018-03-25 09:12:33',NULL,4,1),(71,'ba54fabff6b774b1cd23b207a173542d04fb7828dbfb1eb000e754fe8f36b967','2018-03-25 09:12:33',NULL,5,1);
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

-- Dump completed on 2018-02-23  0:17:58
