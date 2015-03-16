<?php

/**
 * AddTables - Migration to initialize DB-structure
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 3 of
 * the License, or (at your option) any later version.
 *
 * @author      Till Glöggler <tgloeggl@uos.de>
 * @author      Stephan Mielke
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GPL version 3
 * @category    Stud.IP
 */
class AddTables extends Migration
{
    function up()
    {
        DBManager::get()->exec("
            CREATE TABLE IF NOT EXISTS `leeroy_config` (
              `seminar_id` varchar(32) NOT NULL,
              `jenkins_url` text,
              `jenkins_user` text,
              `jenkins_token` text,
              `force_data` tinyint(1) NOT NULL DEFAULT '0',
              `use_ssl` tinyint(1) NOT NULL DEFAULT '0',
              `aux` mediumtext,
              `use_jenkins` tinyint(1) NOT NULL DEFAULT '0',
              `group_sync_regex` varchar(45) DEFAULT NULL,
              PRIMARY KEY (`seminar_id`)
            )
        ");

        DBManager::get()->exec("
            CREATE TABLE IF NOT EXISTS `leeroy_tasks` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `seminar_id` varchar(32) NOT NULL,
              `title` varchar(255) DEFAULT NULL,
              `content` mediumtext,
              `allow_text` tinyint(1) DEFAULT '0',
              `allow_files` tinyint(1) DEFAULT '0',
              `startdate` int(11) DEFAULT NULL,
              `enddate` int(11) DEFAULT NULL,
              `chdate` int(11) DEFAULT NULL,
              `mkdate` int(11) DEFAULT NULL,
              `task_link` mediumtext,
              `required` tinyint(1) DEFAULT '1',
              `is_active` tinyint(1) DEFAULT '0',
              `analytic` mediumtext,
              `test` mediumtext,
              `link` varchar(2083) DEFAULT NULL,
              `lastJob` enum('fail') DEFAULT NULL,
              `log` mediumtext,
              PRIMARY KEY (`id`),
              KEY `task_seminar_idx` (`seminar_id`),
              KEY `leeroy_tasks_enddate_idx` (`enddate`),
              CONSTRAINT `config_task_fk` FOREIGN KEY (`seminar_id`) REFERENCES `leeroy_config` (`seminar_id`) ON DELETE CASCADE ON UPDATE CASCADE
            )
        ");

        DBManager::get()->exec("
            CREATE TABLE IF NOT EXISTS `leeroy_task_files` (
              `dokument_id` varchar(32) NOT NULL,
              `task_id` int(11) NOT NULL,
              PRIMARY KEY (`dokument_id`),
              KEY `task_files_idx` (`task_id`),
              CONSTRAINT `task_files_fk` FOREIGN KEY (`task_id`) REFERENCES `leeroy_tasks` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
            )
        ");

        DBManager::get()->exec("
            CREATE TABLE IF NOT EXISTS `leeroy_handin` (
              `task_id` int(11) NOT NULL,
              `user_id` varchar(32) NOT NULL,
              `hint` mediumtext,
              `answer` mediumtext,
              `feedback` mediumtext,
              `chdate` int(11) DEFAULT NULL,
              `mkdate` int(11) DEFAULT NULL,
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `points` int(11) DEFAULT NULL,
              `analytic` mediumtext,
              `test` mediumtext,
              `link` varchar(45) DEFAULT NULL,
              `lastJob` enum('fail') DEFAULT NULL,
              `log` mediumtext,
              PRIMARY KEY (`id`),
              KEY `user_handin_idx` (`user_id`),
              KEY `task_handin_idx` (`task_id`),
              CONSTRAINT `task_handin_fk` FOREIGN KEY (`task_id`) REFERENCES `leeroy_tasks` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
            )
        ");

        DBManager::get()->exec("
            CREATE TABLE IF NOT EXISTS `leeroy_handin_files` (
              `dokument_id` varchar(32) NOT NULL,
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `handin_id` int(11) NOT NULL,
              `type` enum('answer','feedback') NOT NULL DEFAULT 'answer',
              PRIMARY KEY (`id`),
              KEY `handin_files_idx` (`handin_id`),
              CONSTRAINT `task_handin_filex_fk` FOREIGN KEY (`handin_id`) REFERENCES `leeroy_handin` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
            )
        ");

        DBManager::get()->exec("
            CREATE TABLE IF NOT EXISTS `leeroy_job` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `task_id` int(11) NOT NULL,
              `name` varchar(45) NOT NULL,
              `dokument_id` varchar(32) DEFAULT NULL,
              `trigger` enum('upload','end','end_all') NOT NULL,
              `description` text NOT NULL,
              PRIMARY KEY (`id`),
              KEY `job_task` (`task_id`),
              CONSTRAINT `job_task_fk` FOREIGN KEY (`task_id`) REFERENCES `leeroy_tasks` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
            )
        ");

        DBManager::get()->exec("
            CREATE TABLE IF NOT EXISTS `leeroy_job_build` (
              `token` varchar(32) NOT NULL,
              `job_id` int(11) NOT NULL,
              `handin_file_id` varchar(32) DEFAULT NULL,
              PRIMARY KEY (`token`),
              KEY `job_build_idx` (`job_id`),
              KEY `build_handin_file` (`handin_file_id`),
              CONSTRAINT `build_handin_file` FOREIGN KEY (`handin_file_id`) REFERENCES `leeroy_handin_files` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
              CONSTRAINT `job_build_fk` FOREIGN KEY (`job_id`) REFERENCES `leeroy_job` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
            )
        ");

        DBManager::get()->exec("
            CREATE TABLE IF NOT EXISTS `leeroy_job_timetrigger` (
              `job_id` int(11) NOT NULL,
              `time` int(11) NOT NULL,
              `worker` varchar(45) DEFAULT NULL,
              PRIMARY KEY (`job_id`),
              KEY `timetrigger_task_endtime_fk_idx` (`time`),
              CONSTRAINT `tt_job_fk` FOREIGN KEY (`job_id`) REFERENCES `leeroy_job` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
              CONSTRAINT `tt_time_fk` FOREIGN KEY (`time`) REFERENCES `leeroy_tasks` (`enddate`) ON DELETE CASCADE ON UPDATE CASCADE
            )
        ");
    }

    function down()
    {
        DBManager::get()->exec("DROP TABLE `leeroy_job_timetrigger`");
        DBManager::get()->exec("DROP TABLE `leeroy_job_build`");
        DBManager::get()->exec("DROP TABLE `leeroy_handin_files`");
        DBManager::get()->exec("DROP TABLE `leeroy_handin`");
        DBManager::get()->exec("DROP TABLE `leeroy_job`");
        DBManager::get()->exec("DROP TABLE `leeroy_task_files`");
        DBManager::get()->exec("DROP TABLE `leeroy_tasks`");
        DBManager::get()->exec("DROP TABLE `leeroy_config`");
    }
}
