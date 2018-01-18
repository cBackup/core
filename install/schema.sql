
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
-- Table structure for table `alt_interface`
--

DROP TABLE IF EXISTS `alt_interface`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `alt_interface` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `node_id` int(11) NOT NULL,
  `ip` varchar(15) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_alt_interfaces_nodes1_idx` (`node_id`),
  CONSTRAINT `fk_alt_interface_node1` FOREIGN KEY (`node_id`) REFERENCES `node` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `auth_assignment`
--

DROP TABLE IF EXISTS `auth_assignment`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `auth_assignment` (
  `item_name` varchar(64) NOT NULL,
  `user_id` varchar(64) NOT NULL,
  `created_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`item_name`,`user_id`),
  KEY `fk_auth_assignment_user` (`user_id`),
  CONSTRAINT `auth_assignment_ibfk_1` FOREIGN KEY (`item_name`) REFERENCES `auth_item` (`name`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_auth_assignment_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`userid`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `auth_item`
--

DROP TABLE IF EXISTS `auth_item`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `auth_item` (
  `name` varchar(64) NOT NULL,
  `type` int(11) NOT NULL,
  `description` text,
  `rule_name` varchar(64) DEFAULT NULL,
  `data` text,
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`name`),
  KEY `rule_name` (`rule_name`),
  KEY `idx-auth_item-type` (`type`),
  CONSTRAINT `auth_item_ibfk_1` FOREIGN KEY (`rule_name`) REFERENCES `auth_rule` (`name`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `auth_item_child`
--

DROP TABLE IF EXISTS `auth_item_child`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `auth_item_child` (
  `parent` varchar(64) NOT NULL,
  `child` varchar(64) NOT NULL,
  PRIMARY KEY (`parent`,`child`),
  KEY `child` (`child`),
  CONSTRAINT `auth_item_child_ibfk_1` FOREIGN KEY (`parent`) REFERENCES `auth_item` (`name`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `auth_item_child_ibfk_2` FOREIGN KEY (`child`) REFERENCES `auth_item` (`name`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `auth_rule`
--

DROP TABLE IF EXISTS `auth_rule`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `auth_rule` (
  `name` varchar(64) NOT NULL,
  `data` text,
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `config`
--

DROP TABLE IF EXISTS `config`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `config` (
  `key` varchar(64) NOT NULL,
  `value` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `credential`
--

DROP TABLE IF EXISTS `credential`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `credential` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(128) NOT NULL,
  `telnet_login` varchar(128) DEFAULT NULL,
  `telnet_password` varchar(128) DEFAULT NULL,
  `ssh_login` varchar(128) DEFAULT NULL,
  `ssh_password` varchar(128) DEFAULT NULL,
  `snmp_read` varchar(128) DEFAULT NULL,
  `snmp_set` varchar(128) DEFAULT NULL,
  `snmp_version` tinyint(1) NOT NULL DEFAULT '1',
  `snmp_encryption` varchar(128) DEFAULT NULL,
  `enable_password` varchar(128) DEFAULT NULL,
  `port_telnet` smallint(5) unsigned DEFAULT '23',
  `port_ssh` smallint(5) unsigned DEFAULT '22',
  `port_snmp` smallint(5) unsigned DEFAULT '161',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `device`
--

DROP TABLE IF EXISTS `device`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `device` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `vendor` varchar(64) NOT NULL,
  `model` varchar(128) NOT NULL,
  `auth_template_name` varchar(64) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `vendor_model_UNIQUE` (`vendor`,`model`),
  KEY `fk_device_auth_template1` (`auth_template_name`),
  CONSTRAINT `fk_device_auth_template1` FOREIGN KEY (`auth_template_name`) REFERENCES `device_auth_template` (`name`) ON UPDATE CASCADE,
  CONSTRAINT `fk_device_vendor1` FOREIGN KEY (`vendor`) REFERENCES `vendor` (`name`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `device_attributes`
--

DROP TABLE IF EXISTS `device_attributes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `device_attributes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `device_id` int(11) NOT NULL,
  `sysobject_id` varchar(255) DEFAULT NULL,
  `hw` varchar(255) DEFAULT NULL,
  `sys_description` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ix_snmp` (`sysobject_id`,`hw`,`sys_description`),
  KEY `device_attributes_device1` (`device_id`),
  CONSTRAINT `device_attributes_device1` FOREIGN KEY (`device_id`) REFERENCES `device` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `device_attributes_unknown`
--

DROP TABLE IF EXISTS `device_attributes_unknown`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `device_attributes_unknown` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sysobject_id` varchar(255) DEFAULT NULL,
  `hw` varchar(255) DEFAULT NULL,
  `sys_description` varchar(255) DEFAULT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ix_snmp` (`sysobject_id`,`hw`,`sys_description`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `device_auth_template`
--

DROP TABLE IF EXISTS `device_auth_template`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `device_auth_template` (
  `name` varchar(64) NOT NULL,
  `auth_sequence` text NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `exclusion`
--

DROP TABLE IF EXISTS `exclusion`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `exclusion` (
  `ip` varchar(15) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`ip`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `job`
--

DROP TABLE IF EXISTS `job`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `job` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `worker_id` int(11) NOT NULL,
  `sequence_id` int(11) NOT NULL,
  `command_value` varchar(255) NOT NULL,
  `command_var` varchar(255) DEFAULT NULL,
  `snmp_request_type` varchar(32) DEFAULT NULL,
  `snmp_set_value` varchar(255) DEFAULT NULL,
  `snmp_set_value_type` varchar(32) DEFAULT NULL,
  `timeout` int(11) DEFAULT NULL,
  `table_field` varchar(255) DEFAULT NULL,
  `enabled` tinyint(1) NOT NULL DEFAULT '1',
  `description` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_job_task_id_idx` (`worker_id`),
  KEY `fk_job_snmp_types_1` (`snmp_set_value_type`),
  KEY `fk_job_snmp_request_types_1` (`snmp_request_type`),
  KEY `ix_sequence1` (`sequence_id`),
  KEY `ix_state` (`enabled`),
  KEY `ix_sequence2` (`enabled`,`sequence_id`),
  CONSTRAINT `fk_job_snmp_request_types_1` FOREIGN KEY (`snmp_request_type`) REFERENCES `job_snmp_request_types` (`name`) ON UPDATE CASCADE,
  CONSTRAINT `fk_job_snmp_types_1` FOREIGN KEY (`snmp_set_value_type`) REFERENCES `job_snmp_types` (`name`) ON UPDATE CASCADE,
  CONSTRAINT `fk_job_worker1` FOREIGN KEY (`worker_id`) REFERENCES `worker` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `job_global_variable`
--

DROP TABLE IF EXISTS `job_global_variable`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `job_global_variable` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `var_name` varchar(128) NOT NULL,
  `var_value` varchar(255) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `job_snmp_request_types`
--

DROP TABLE IF EXISTS `job_snmp_request_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `job_snmp_request_types` (
  `name` varchar(32) NOT NULL,
  PRIMARY KEY (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `job_snmp_types`
--

DROP TABLE IF EXISTS `job_snmp_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `job_snmp_types` (
  `name` varchar(32) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `log_mailer`
--

DROP TABLE IF EXISTS `log_mailer`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_mailer` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userid` varchar(128) DEFAULT NULL,
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `severity` varchar(32) NOT NULL,
  `action` varchar(45) DEFAULT NULL,
  `event_task_id` int(11) DEFAULT NULL,
  `message` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_log_mailer_user1` (`userid`),
  KEY `fk_log_mailer_severity1` (`severity`),
  KEY `fk_log_mailer_mail1` (`event_task_id`),
  CONSTRAINT `fk_log_mailer_mail1` FOREIGN KEY (`event_task_id`) REFERENCES `mailer_events_tasks` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_log_mailer_severity1` FOREIGN KEY (`severity`) REFERENCES `severity` (`name`) ON UPDATE CASCADE,
  CONSTRAINT `fk_log_mailer_user1` FOREIGN KEY (`userid`) REFERENCES `user` (`userid`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `log_node`
--

DROP TABLE IF EXISTS `log_node`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_node` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `userid` varchar(128) NOT NULL,
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `node_id` int(11) DEFAULT NULL,
  `severity` varchar(32) NOT NULL DEFAULT 'INFO',
  `action` varchar(45) DEFAULT NULL,
  `message` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_node_logs_nodes1_idx` (`node_id`),
  KEY `fk_log_node_user1` (`userid`),
  KEY `fk_log_node_severity1` (`severity`),
  KEY `ix_time` (`time`),
  CONSTRAINT `fk_log_node1` FOREIGN KEY (`node_id`) REFERENCES `node` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_log_node_severity1` FOREIGN KEY (`severity`) REFERENCES `severity` (`name`) ON UPDATE CASCADE,
  CONSTRAINT `fk_log_node_user1` FOREIGN KEY (`userid`) REFERENCES `user` (`userid`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `log_scheduler`
--

DROP TABLE IF EXISTS `log_scheduler`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_scheduler` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userid` varchar(128) DEFAULT NULL,
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `severity` varchar(32) NOT NULL DEFAULT 'INFO',
  `schedule_type` varchar(32) NOT NULL DEFAULT 'scheduled',
  `schedule_id` int(11) DEFAULT NULL,
  `node_id` int(11) DEFAULT NULL,
  `action` varchar(45) DEFAULT NULL,
  `message` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_log_schedule_node_id_idx` (`node_id`),
  KEY `fk_log_scheduler_schedule1_idx` (`schedule_id`),
  KEY `fk_log_scheduler_user1` (`userid`),
  KEY `fk_log_scheduler_severity1` (`severity`),
  KEY `ix_time` (`time`),
  KEY `fk_log_scheduler_schedule_types_1` (`schedule_type`),
  CONSTRAINT `fk_log_scheduler_node1` FOREIGN KEY (`node_id`) REFERENCES `node` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_log_scheduler_schedule1` FOREIGN KEY (`schedule_id`) REFERENCES `schedule` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_log_scheduler_schedule_types_1` FOREIGN KEY (`schedule_type`) REFERENCES `schedule_type` (`name`) ON UPDATE CASCADE,
  CONSTRAINT `fk_log_scheduler_severity1` FOREIGN KEY (`severity`) REFERENCES `severity` (`name`) ON UPDATE CASCADE,
  CONSTRAINT `fk_log_scheduler_user1` FOREIGN KEY (`userid`) REFERENCES `user` (`userid`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `log_system`
--

DROP TABLE IF EXISTS `log_system`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_system` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `userid` varchar(128) DEFAULT NULL,
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `severity` varchar(32) NOT NULL DEFAULT 'INFO',
  `action` varchar(45) DEFAULT NULL,
  `message` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_log_system_user1` (`userid`),
  KEY `fk_log_system_severity` (`severity`),
  KEY `ix_time` (`time`),
  CONSTRAINT `fk_log_system_severity` FOREIGN KEY (`severity`) REFERENCES `severity` (`name`) ON UPDATE CASCADE,
  CONSTRAINT `fk_log_system_user1` FOREIGN KEY (`userid`) REFERENCES `user` (`userid`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='		';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `mailer_events`
--

DROP TABLE IF EXISTS `mailer_events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mailer_events` (
  `name` varchar(128) NOT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `template` text,
  `recipients` text,
  `description` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `mailer_events_tasks`
--

DROP TABLE IF EXISTS `mailer_events_tasks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mailer_events_tasks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `event_name` varchar(128) NOT NULL,
  `status` varchar(64) NOT NULL DEFAULT 'new',
  `subject` varchar(255) NOT NULL,
  `body` text NOT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_event_tasks_statuses1` (`status`),
  KEY `fk_event_tasks_events1` (`event_name`),
  CONSTRAINT `fk_event_tasks_events1` FOREIGN KEY (`event_name`) REFERENCES `mailer_events` (`name`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_event_tasks_statuses1` FOREIGN KEY (`status`) REFERENCES `mailer_events_tasks_statuses` (`name`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `mailer_events_tasks_statuses`
--

DROP TABLE IF EXISTS `mailer_events_tasks_statuses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mailer_events_tasks_statuses` (
  `name` varchar(64) NOT NULL,
  PRIMARY KEY (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `messages`
--

DROP TABLE IF EXISTS `messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `messages` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `message` text NOT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `approved` timestamp NULL DEFAULT NULL,
  `approved_by` varchar(128) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_messages_user1` (`approved_by`),
  KEY `ix_time` (`created`,`approved`) USING BTREE,
  CONSTRAINT `fk_messages_user1` FOREIGN KEY (`approved_by`) REFERENCES `user` (`userid`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `migration`
--

DROP TABLE IF EXISTS `migration`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `migration` (
  `version` varchar(180) NOT NULL,
  `apply_time` int(11) DEFAULT NULL,
  PRIMARY KEY (`version`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `network`
--

DROP TABLE IF EXISTS `network`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `network` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `credential_id` int(11) NOT NULL,
  `network` varchar(18) NOT NULL,
  `discoverable` tinyint(1) NOT NULL DEFAULT '1',
  `description` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `source_UNIQUE` (`network`),
  KEY `fk_discovery_credentials1_idx` (`credential_id`),
  CONSTRAINT `fk_discovery_credentials1` FOREIGN KEY (`credential_id`) REFERENCES `credential` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `node`
--

DROP TABLE IF EXISTS `node`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `node` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ip` varchar(15) NOT NULL,
  `network_id` int(11) DEFAULT NULL,
  `credential_id` int(11) DEFAULT NULL,
  `device_id` int(11) NOT NULL,
  `auth_template_name` varchar(64) DEFAULT NULL,
  `mac` varchar(12) DEFAULT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified` timestamp NULL DEFAULT NULL,
  `last_seen` timestamp NULL DEFAULT NULL,
  `manual` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `hostname` varchar(255) DEFAULT NULL,
  `serial` varchar(45) DEFAULT NULL,
  `prepend_location` varchar(255) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `contact` varchar(255) DEFAULT NULL,
  `sys_description` varchar(255) DEFAULT NULL,
  `protected` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `node_ip_unique` (`ip`),
  UNIQUE KEY `mac_UNIQUE` (`mac`),
  KEY `fk_devices_device_models_idx` (`device_id`),
  KEY `fk_node_credentials1` (`credential_id`),
  KEY `fk_node_network1` (`network_id`),
  KEY `ix_hostname` (`hostname`),
  KEY `ix_location` (`location`),
  KEY `fk_node_auth_template1` (`auth_template_name`),
  CONSTRAINT `fk_node_auth_template1` FOREIGN KEY (`auth_template_name`) REFERENCES `device_auth_template` (`name`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_node_credentials1` FOREIGN KEY (`credential_id`) REFERENCES `credential` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_node_device1` FOREIGN KEY (`device_id`) REFERENCES `device` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_node_network1` FOREIGN KEY (`network_id`) REFERENCES `network` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `out_backup`
--

DROP TABLE IF EXISTS `out_backup`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `out_backup` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `node_id` int(11) NOT NULL,
  `hash` varchar(255) NOT NULL,
  `config` longtext,
  PRIMARY KEY (`id`),
  UNIQUE KEY `out_backup_node_id_unique` (`node_id`),
  CONSTRAINT `fk_out_backup_node1` FOREIGN KEY (`node_id`) REFERENCES `node` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `out_stp`
--

DROP TABLE IF EXISTS `out_stp`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `out_stp` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `node_id` int(11) NOT NULL,
  `hash` varchar(255) NOT NULL,
  `node_mac` varchar(255) DEFAULT NULL,
  `root_port` varchar(255) DEFAULT NULL,
  `root_mac` varchar(255) DEFAULT NULL,
  `bridge_mac` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `out_stp_node_id_unique` (`node_id`),
  CONSTRAINT `fk_out_stp_node1` FOREIGN KEY (`node_id`) REFERENCES `node` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `plugin`
--

DROP TABLE IF EXISTS `plugin`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `plugin` (
  `name` varchar(64) NOT NULL,
  `author` varchar(255) NOT NULL,
  `version` varchar(32) NOT NULL,
  `access` varchar(64) DEFAULT 'admin',
  `enabled` tinyint(1) DEFAULT '0',
  `widget` varchar(255) DEFAULT NULL,
  `metadata` text NOT NULL,
  `params` text,
  `description` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`name`),
  KEY `fk_plugin1` (`access`),
  KEY `ix_plugin1` (`enabled`,`widget`),
  CONSTRAINT `fk_plugin1` FOREIGN KEY (`access`) REFERENCES `auth_item` (`name`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `schedule`
--

DROP TABLE IF EXISTS `schedule`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `schedule` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `task_name` varchar(255) NOT NULL,
  `schedule_cron` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `schedule_task_name_unique` (`task_name`),
  KEY `fk_scheduler_task1_idx` (`task_name`),
  CONSTRAINT `fk_scheduler_task1` FOREIGN KEY (`task_name`) REFERENCES `task` (`name`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `schedule_mail`
--

DROP TABLE IF EXISTS `schedule_mail`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `schedule_mail` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `event_name` varchar(128) NOT NULL,
  `schedule_cron` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `schedule_event_name_unique` (`event_name`),
  CONSTRAINT `fk_scheduler_event1` FOREIGN KEY (`event_name`) REFERENCES `mailer_events` (`name`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `schedule_type`
--

DROP TABLE IF EXISTS `schedule_type`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `schedule_type` (
  `name` varchar(32) NOT NULL,
  PRIMARY KEY (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `setting`
--

DROP TABLE IF EXISTS `setting`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `setting` (
  `key` varchar(64) NOT NULL,
  `value` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `setting_override`
--

DROP TABLE IF EXISTS `setting_override`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `setting_override` (
  `key` varchar(64) NOT NULL,
  `userid` varchar(128) NOT NULL,
  `value` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`key`,`userid`),
  KEY `fk_setting_override_userid_user_userid` (`userid`),
  CONSTRAINT `fk_setting_override_key_setting_key` FOREIGN KEY (`key`) REFERENCES `setting` (`key`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_setting_override_userid_user_userid` FOREIGN KEY (`userid`) REFERENCES `user` (`userid`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `severity`
--

DROP TABLE IF EXISTS `severity`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `severity` (
  `name` varchar(32) NOT NULL,
  PRIMARY KEY (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `task`
--

DROP TABLE IF EXISTS `task`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `task` (
  `name` varchar(255) NOT NULL,
  `put` varchar(16) DEFAULT NULL,
  `table` varchar(255) DEFAULT NULL,
  `task_type` varchar(32) NOT NULL DEFAULT 'node_task',
  `yii_command` varchar(255) DEFAULT NULL,
  `protected` tinyint(1) NOT NULL DEFAULT '0',
  `description` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`name`) USING BTREE,
  KEY `ix_destination` (`put`,`name`),
  KEY `fk_task_task_type1` (`task_type`),
  CONSTRAINT `fk_task_task_destination1` FOREIGN KEY (`put`) REFERENCES `task_destination` (`name`) ON UPDATE CASCADE,
  CONSTRAINT `fk_task_task_type1` FOREIGN KEY (`task_type`) REFERENCES `task_type` (`name`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `task_destination`
--

DROP TABLE IF EXISTS `task_destination`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `task_destination` (
  `name` varchar(16) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `task_type`
--

DROP TABLE IF EXISTS `task_type`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `task_type` (
  `name` varchar(32) NOT NULL,
  PRIMARY KEY (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tasks_has_devices`
--

DROP TABLE IF EXISTS `tasks_has_devices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tasks_has_devices` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `task_name` varchar(255) NOT NULL,
  `device_id` int(11) NOT NULL,
  `worker_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_schedule_model_template_schedule1_idx` (`task_name`),
  KEY `fk_schedule_model_template_model1_idx` (`device_id`),
  KEY `fk_schedule_model_task_id_idx` (`worker_id`),
  CONSTRAINT `fk_tasks_has_devices_device1` FOREIGN KEY (`device_id`) REFERENCES `device` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_tasks_has_devices_task1` FOREIGN KEY (`task_name`) REFERENCES `task` (`name`) ON UPDATE CASCADE,
  CONSTRAINT `fk_tasks_has_devices_worker1` FOREIGN KEY (`worker_id`) REFERENCES `worker` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tasks_has_nodes`
--

DROP TABLE IF EXISTS `tasks_has_nodes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tasks_has_nodes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `node_id` int(11) NOT NULL,
  `task_name` varchar(255) NOT NULL,
  `worker_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_nodes_has_schedules_nodes1_idx` (`node_id`),
  KEY `fk_nodes_has_schedules_schedule1_idx` (`task_name`),
  KEY `fk_schedules_has_nodes_task_id_idx` (`worker_id`),
  CONSTRAINT `fk_tasks_has_nodes_node1` FOREIGN KEY (`node_id`) REFERENCES `node` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_tasks_has_nodes_task1` FOREIGN KEY (`task_name`) REFERENCES `task` (`name`) ON UPDATE CASCADE,
  CONSTRAINT `fk_tasks_has_nodes_worker1` FOREIGN KEY (`worker_id`) REFERENCES `worker` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user`
--

DROP TABLE IF EXISTS `user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user` (
  `userid` varchar(128) NOT NULL,
  `auth_key` varchar(32) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `access_token` varchar(128) DEFAULT NULL,
  `fullname` varchar(128) NOT NULL,
  `email` varchar(128) DEFAULT NULL,
  `enabled` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`userid`),
  UNIQUE KEY `email` (`email`),
  KEY `ix_user` (`access_token`,`enabled`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `vendor`
--

DROP TABLE IF EXISTS `vendor`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `vendor` (
  `name` varchar(64) NOT NULL,
  PRIMARY KEY (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `worker`
--

DROP TABLE IF EXISTS `worker`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `worker` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `task_name` varchar(255) NOT NULL,
  `get` varchar(16) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_task_protocol_idx` (`get`),
  KEY `fk_worker_task1` (`task_name`),
  CONSTRAINT `fk_worker_task1` FOREIGN KEY (`task_name`) REFERENCES `task` (`name`) ON UPDATE CASCADE,
  CONSTRAINT `fk_worker_worker_protocol1` FOREIGN KEY (`get`) REFERENCES `worker_protocol` (`name`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `worker_protocol`
--

DROP TABLE IF EXISTS `worker_protocol`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `worker_protocol` (
  `name` varchar(16) NOT NULL,
  PRIMARY KEY (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
