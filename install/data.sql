-- ------------------------------------------------------------------------------------------------
-- Jobs and tasks
-- ------------------------------------------------------------------------------------------------
INSERT INTO `job_snmp_request_types` (`name`) VALUES
  ('get'),
  ('set');

INSERT INTO `task_destination` (`name`, `description`) VALUES
  ('db', 'Database'),
  ('file', 'File storage');

INSERT INTO `job_snmp_types` (`name`, `description`) VALUES
  ('hex_string', 'Hex string'),
  ('int', 'Integer'),
  ('ip_address', 'IP address'),
  ('null', 'Null'),
  ('octet_string', 'Octet string'),
  ('uint', 'Unsigned integer');

INSERT INTO `worker_protocol` (`name`) VALUES
  ('snmp'),
  ('ssh'),
  ('telnet');

INSERT INTO `task_type` (`name`) VALUES
  ('system_task'), ('node_task'), ('yii_console_task'), ('discovery');

INSERT INTO `task` (`name`,`put`,`table`,`task_type`,`yii_command`,`protected`,`description`) VALUES
  ('backup','file','out_backup','node_task',NULL,'1','Get nodes config'),
  ('discovery',NULL,NULL,'discovery',NULL,'1','Node discovery'),
  ('git_commit',NULL,NULL,'system_task',NULL,'1','Git commit'),
  ('log_processing',NULL,NULL,'system_task',NULL,'1','Log clearing'),
  ('node_processing',NULL,NULL,'system_task',NULL,'1','Old nodes clearing'),
  ('save',NULL,NULL,'node_task',NULL,'1','Save nodes configuration'),
  ('stp','db','out_stp','node_task',NULL,'1','Get nodes STP info');

INSERT INTO `schedule_type` (`name`) VALUES
  ('scheduled'), ('manual'), ('manual_single_node');

-- ------------------------------------------------------------------------------------------------
-- Logging
-- ------------------------------------------------------------------------------------------------
INSERT INTO `severity` (`name`) VALUES
  ('ALERT'),
  ('CRITICAL'),
  ('DEBUG'),
  ('EMERG'),
  ('ERROR'),
  ('INFO'),
  ('NOTICE'),
  ('WARNING');

-- ------------------------------------------------------------------------------------------------
-- Configuration and settings
-- Commented strings are inserted in InstallController::actionIntegrity()
-- ------------------------------------------------------------------------------------------------
INSERT INTO `config` (`key`, `value`) VALUES
  -- adminEmail
  -- dataPath
  ('defaultPrependLocation', NULL),
  ('git', '1'),
  ('gitDays', '30'),
  -- gitEmail
  ('gitLogin', NULL),
  ('gitPassword', NULL),
  -- gitPath
  ('gitRemote', '0'),
  ('gitRepo', NULL),
  ('gitUsername', 'cBackup Service'),
  -- isolated
  ('javaHost', '127.0.0.1'),
  -- javaSchedulerPassword
  -- javaSchedulerPort
  -- javaSchedulerUsername
  -- javaServerPassword
  -- javaServerPort
  -- javaServerUsername
  ('logLifetime', '7'),
  ('mailer', '0'),
  ('mailerFromEmail', NULL),
  ('mailerFromName', NULL),
  ('mailerSendMailPath', '/usr/sbin/sendmail'),
  ('mailerSmtpAuth', '0'),
  ('mailerSmtpHost', NULL),
  ('mailerSmtpPassword', NULL),
  ('mailerSmtpPort', '25'),
  ('mailerSmtpSecurity', 'none'),
  ('mailerSmtpSslVerify', '0'),
  ('mailerSmtpUsername', NULL),
  ('mailerType', 'local'),
  ('nodeLifetime', '0'),
  ('snmpRetries', '3'),
  ('snmpTimeout', '500'),
  ('sshBeforeSendDelay', '1000'),
  ('sshTimeout', '10000'),
  ('systemLogLevel', 'INFO'),
  ('telnetBeforeSendDelay', '100'),
  ('telnetTimeout', '10000')
  -- threadCount
;

INSERT INTO `setting` (`key`, `value`) VALUES
  ('sidebar_collapsed', '0'),
  ('language', 'en-US'),
  ('date', 'Y-m-d'),
  ('datetime', 'Y-m-d H:i:s')
;

-- ------------------------------------------------------------------------------------------------
-- Mail subsystem
-- ------------------------------------------------------------------------------------------------
INSERT INTO `mailer_events_tasks_statuses` (`name`) VALUES
  ('new'), ('outgoing'), ('sent');

-- ------------------------------------------------------------------------------------------------
-- Create users
-- ------------------------------------------------------------------------------------------------
INSERT INTO `auth_item` (`name`, `type`, `description`, `rule_name`, `data`, `created_at`, `updated_at`) VALUES
  ('admin', 1, 'System administrators', NULL, NULL, 1482417888, NULL),
  ('APICore', 2, 'Access to API V1 methods. User with this permission can write and read data.', NULL, NULL, 1483519196, NULL),
  ('APIReader', 2, 'Access to API V2 methods. User with this permission can only read data.', NULL, NULL, 1483519033, NULL);

INSERT INTO `auth_item_child` (`parent`, `child`) VALUES
  ('admin', 'APIReader');

-- ------------------------------------------------------------------------------------------------
-- Vendors and devices
-- ------------------------------------------------------------------------------------------------
INSERT INTO `vendor` (`name`) VALUES
  ('Arris'),
  ('Cisco'),
  ('D-link'),
  ('Extreme'),
  ('Mikrotik'),
  ('Zyxel');
