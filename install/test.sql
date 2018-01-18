-- noinspection SqlResolveForFile

SET FOREIGN_KEY_CHECKS = 0;
TRUNCATE TABLE `vendor`;
TRUNCATE TABLE `credential`;
TRUNCATE TABLE `device_auth_template`;
TRUNCATE TABLE `device`;
TRUNCATE TABLE `exclusion`;
TRUNCATE TABLE `network`;
TRUNCATE TABLE `node`;
TRUNCATE TABLE `out_backup`;
TRUNCATE TABLE `job_snmp_request_types`;
TRUNCATE TABLE `job_snmp_types`;
TRUNCATE TABLE `worker_protocol`;
TRUNCATE TABLE `task_type`;
TRUNCATE TABLE `task_destination`;
TRUNCATE TABLE `task`;
TRUNCATE TABLE `schedule`;
TRUNCATE TABLE `worker`;
TRUNCATE TABLE `tasks_has_nodes`;
TRUNCATE TABLE `job`;
TRUNCATE TABLE `severity`;
TRUNCATE TABLE `config`;
TRUNCATE TABLE `setting`;
TRUNCATE TABLE `mailer_events_tasks_statuses`;
TRUNCATE TABLE `auth_item`;
TRUNCATE TABLE `user`;
TRUNCATE TABLE `auth_assignment`;
TRUNCATE TABLE `auth_item_child`;
SET FOREIGN_KEY_CHECKS = 1;

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

INSERT INTO `credential` (`id`, `name`, `telnet_login`, `telnet_password`, `ssh_login`, `ssh_password`, `snmp_read`, `snmp_set`, `snmp_version`, `snmp_encryption`, `enable_password`, `port_telnet`, `port_ssh`, `port_snmp`) VALUES
  (1, 'test_credential', 'telnet_login', 'telnet_pass', 'ssh_login', 'ssh_pass', 'public', 'private', 1, NULL, NULL, 23, 22, 161);

INSERT INTO `device_auth_template` (`name`, `auth_sequence`, `description`) VALUES
  ('d_link_auth', 'ame:\r\n{{telnet_login}}\r\nord:\r\n{{telnet_password}}\r\n#', NULL);

INSERT INTO `device` (`id`, `vendor`, `model`, `auth_template_name`) VALUES
  (1, 'D-link', 'DES_3018', 'd_link_auth');

INSERT INTO `exclusion` (`ip`, `description`) VALUES
  ('192.168.0.1', 'Some exclusion');

INSERT INTO `network` (`id`, `credential_id`, `network`, `discoverable`, `description`) VALUES
  (1, 1, '192.168.0.0/26', 1, 'Test Subnet');

INSERT INTO `node` (`id`, `ip`, `network_id`, `credential_id`, `device_id`, `auth_template_name`, `mac`, `created`, `modified`, `last_seen`, `manual`, `hostname`, `serial`, `prepend_location`, `location`, `contact`, `sys_description`) VALUES
  (1, '192.168.0.2', NULL, 1, 1, NULL, '000000000000', '2017-12-14 09:08:32', NULL, NULL, 1, '[Metro-Test][Raina35]', '123456789', NULL, 'Raina35', 'admin@example.com', 'Dlink DES-3018 Fast Ethernet Switch');

INSERT INTO `out_backup` (`id`, `time`, `node_id`, `hash`, `config`) VALUES
  (1, '2017-12-14 11:36:57', 1, 'FAB7802EC278DFB6D35468AFD205570C', NULL);

-- ------------------------------------------------------------------------------------------------
-- Jobs and tasks
-- ------------------------------------------------------------------------------------------------
INSERT INTO `job_snmp_request_types` (`name`) VALUES
  ('get'),
  ('set');

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
  ('discovery'),
  ('node_task'),
  ('system_task'),
  ('yii_console_task');

INSERT INTO `task_destination` (`name`, `description`) VALUES
  ('db', 'Database'),
  ('file', 'File storage');

INSERT INTO `task` (`name`, `put`, `table`, `task_type`, `yii_command`, `protected`, `description`) VALUES
  ('backup', 'file', 'out_backup', 'node_task', NULL, 1, 'Get nodes config'),
  ('discovery', NULL, NULL, 'discovery', NULL, 1, 'Node discovery'),
  ('git_commit', NULL, NULL, 'system_task', NULL, 1, 'Git commit'),
  ('log_processing', NULL, NULL, 'system_task', NULL, 1, 'Log clearing'),
  ('node_processing', NULL, NULL, 'system_task', NULL, 1, 'Old nodes clearing'),
  ('save', NULL, NULL, 'node_task', NULL, 1, 'Save nodes configuration'),
  ('stp', 'db', 'out_stp', 'node_task', NULL, 1, 'Get nodes STP info');

INSERT INTO `schedule` (`id`, `task_name`, `schedule_cron`) VALUES
  (1, 'backup', '1 0 * * *');

INSERT INTO `worker` (`id`, `name`, `task_name`, `get`, `description`) VALUES
  (2, 'd_link_backup', 'backup', 'telnet', NULL);

INSERT INTO `tasks_has_nodes` (`id`, `node_id`, `task_name`, `worker_id`) VALUES
  (1, 1, 'backup', 2);

INSERT INTO `job` (`id`, `name`, `worker_id`, `sequence_id`, `command_value`, `command_var`, `snmp_request_type`, `snmp_set_value`, `snmp_set_value_type`, `timeout`, `table_field`, `enabled`, `description`) VALUES
  (1, 'disable_clipaging', 2, 1, 'disable clipaging', NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL),
  (2, 'show_config', 2, 2, 'show config current_config', NULL, NULL, NULL, NULL, 60000, 'config', 1, NULL),
  (3, 'enable_clipaging', 2, 3, 'enable clipaging', NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL),
  (4, 'logout', 2, 4, 'logout', NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL);

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
-- ------------------------------------------------------------------------------------------------
INSERT INTO `config` (`key`, `value`) VALUES
  ('adminEmail', 'admin@example.com'),
  ('dataPath', '/opt/cbackup/data'),
  ('defaultPrependLocation', NULL),
  ('git', '1'),
  ('gitDays', '30'),
  ('gitEmail', 'admin@example.com'),
  ('gitLogin', NULL),
  ('gitPassword', NULL),
  ('gitPath', '/usr/bin/git'),
  ('gitRemote', '0'),
  ('gitRepo', NULL),
  ('gitUsername', 'cBackup Service'),
  ('isolated', '0'),
  ('javaHost', '127.0.0.1'),
  ('javaSchedulerPassword', 'dJ0h09RT'),
  ('javaSchedulerPort', '8437'),
  ('javaSchedulerUsername', 'cbadmin'),
  ('javaServerPassword', 'admin'),
  ('javaServerPort', '22'),
  ('javaServerUsername', 'cbackup'),
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
  ('telnetTimeout', '10000'),
  ('threadCount', '28');

INSERT INTO `setting` (`key`, `value`) VALUES
  ('date', 'Y-m-d'),
  ('datetime', 'Y-m-d H:i:s'),
  ('language', 'en-US'),
  ('sidebar_collapsed', '0');

-- ------------------------------------------------------------------------------------------------
-- Mail subsystem
-- ------------------------------------------------------------------------------------------------
INSERT INTO `mailer_events_tasks_statuses` (`name`) VALUES
  ('new'),
  ('outgoing'),
  ('sent');

-- ------------------------------------------------------------------------------------------------
-- User subsystem
-- ------------------------------------------------------------------------------------------------

INSERT INTO `auth_item` (`name`, `type`, `description`, `rule_name`, `data`, `created_at`, `updated_at`) VALUES
('admin', 1, 'System administrators', NULL, NULL, 1482417888, NULL),
('APICore', 2, 'Access to API V1 methods. User with this permission can write and read data.', NULL, NULL, 1483519196, NULL),
('APIReader', 2, 'Access to API V2 methods. User with this permission can only read data.', NULL, NULL, 1483519033, NULL);

INSERT INTO `user` (`userid`, `auth_key`, `password_hash`, `access_token`, `fullname`, `email`, `enabled`) VALUES
  ('ADMIN', 'nUbs5HYMrAXAvzoQoZ41ZgS0zvoR6SbO', '$2y$13$XyzYKJ4No7G2NnHR/BzoMOyPUYSBbkj9IYYt9R.PjL0/WenMHl.VO', NULL, 'Admin', 'admin@example.com', 1),
  ('CONSOLE_APP', 'aHR8P_ts_QoeFO_ktGibKw7rgtlZBUAp', '$2y$13$Ygd.8iWljPFQU82xmIFQv.4ZqBf4Ot5BrziBCSyUqC2Eg4yr/nBnW', NULL, 'cBackup Console', NULL, 1),
  ('JAVACORE', 'jI509vwy-spI3hM4mIEdt9c5J5faciVA', '$2y$13$aZiWfTrD3uuyRtcjN9.c7uJitIwvY3kNnvJbvEXmve1jbakNnfHi.', 'A4F6D307-E257-4A0D-A1D2-6793E4286441', 'cBackup Service', NULL, 1);

INSERT INTO `auth_assignment` (`item_name`, `user_id`, `created_at`) VALUES
  ('admin', 'ADMIN', 1513064823),
  ('APICore', 'JAVACORE', 1513064823);

INSERT INTO `auth_item_child` (`parent`, `child`) VALUES
('admin', 'APIReader');
