<?php

/**
 * AddTables - Migration to initialize DB-structure
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 3 of
 * the License, or (at your option) any later version.
 *
 * @author      Till Gl�ggler <tgloeggl@uos.de>
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
              `seminar_id` VARCHAR(32) NULL ,
              `jenkins_url` TEXT NULL,
              `jenkins_user` TEXT NULL,
              `jenkins_token` TEXT NULL,
              PRIMARY KEY (`seminar_id`) )
        ");

        DBManager::get()->exec("
            CREATE TABLE IF NOT EXISTS `leeroy_tasks` (
              `id` INT NOT NULL AUTO_INCREMENT ,
              `seminar_id` VARCHAR(32) NULL ,
              `user_id` VARCHAR(32) NULL ,
              `title` VARCHAR(255) NULL ,
              `content` MEDIUMTEXT NULL ,
              `allow_text` TINYINT(1) NULL DEFAULT 0 ,
              `allow_files` TINYINT(1) NULL DEFAULT 0 ,
              `startdate` INT NULL ,
              `enddate` INT NULL ,
              `send_mail` TINYINT(1) NULL DEFAULT 0 ,
              `chdate` INT NULL ,
              `mkdate` INT NULL ,
              `jenkins_config` INT NOT NULL,
              `build_xml` MEDIUMTEXT NULL,
              `build_file` VARCHAR(32) NULL,
              `task_link` MEDIUMTEXT NULL,
              `task_pdf` VARCHAR(32) NULL,
              `analysis_api` TEXT NULL,
              `test_api` TEXT NULL,
              PRIMARY KEY (`id`))
        ");

        /* DBManager::get()->exec("
             CREATE TABLE IF NOT EXISTS `leeroy_tasks` (
               `id` INT NOT NULL AUTO_INCREMENT ,
               `seminar_id` VARCHAR(32) NULL ,
               `user_id` VARCHAR(32) NULL ,
               `title` VARCHAR(255) NULL ,
               `content` MEDIUMTEXT NULL ,
               `allow_text` TINYINT(1) NULL DEFAULT 0 ,
               `allow_files` TINYINT(1) NULL DEFAULT 0 ,
               `startdate` INT NULL ,
               `enddate` INT NULL ,
               `send_mail` TINYINT(1) NULL DEFAULT 0 ,
               `chdate` INT NULL ,
               `mkdate` INT NULL ,
               # new
               `jenkins_config` INT NOT NULL,    # zugangsdaten f�r jenkins
               #build_template` INT NULL,        # template f�r die aufgabe
               `build_xml` MEDIUMTEXT NULL,      # build xml f�r jenkins
               `build_file` VARCHAR(32) NULL,    # vorgaben wie checkstyle.xml, testf�lle usw
               `task_link` MEDIUMTEXT NULL,      # link zur aufgabe
               `task_pdf` VARCHAR(32) NULL,      # aufgabe als pdf
               `analysis_api` TEXT NULL,         # api call f�r die code analyse (gibt json zur�ck)
               `test_api` TEXT NULL,             # api call f�r die test auswertung (gibt json zur�ck)
               PRIMARY KEY (`id`)#,
               #CONSTRAINT `jenkins_config_fk` FOREIGN KEY (`jenkins_config`)
               #  REFERENCES `leeroy_config`(`seminar_id`)
               #  ON DELETE CASCADE
               )
         ");*/

        DBManager::get()->exec("
            CREATE TABLE IF NOT EXISTS `leeroy_task_users` (
              `id` INT NOT NULL AUTO_INCREMENT ,
              `leeroy_tasks_id` INT NULL ,
              `user_id` VARCHAR(32) NULL ,
              `hint` MEDIUMTEXT NULL ,
              `answer` MEDIUMTEXT NULL ,
              `feedback` MEDIUMTEXT NULL ,
              `visible` TINYINT(1) NULL DEFAULT 1 ,
              `chdate` INT NULL ,
              `mkdate` INT NULL ,
              PRIMARY KEY (`id`) ,
              INDEX `fk_leeroy_tasks_users_leeroy_tasks_id` (`leeroy_tasks_id` ASC) )
        ");


        DBManager::get()->exec("
            CREATE TABLE IF NOT EXISTS `leeroy_task_user_files` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `leeroy_task_users_id` int(11) DEFAULT NULL,
              `dokument_id` varchar(32) NOT NULL,
              `type` enum('answer','feedback') NOT NULL DEFAULT 'answer',
              PRIMARY KEY (`id`)
            )
        ");
    }

    function down()
    {
        DBManager::get()->exec("DROP TABLE `leeroy_config`");
        DBManager::get()->exec("DROP TABLE `leeroy_tasks`");
        DBManager::get()->exec("DROP TABLE `leeroy_task_users`");
        DBManager::get()->exec("DROP TABLE `leeroy_task_user_files`");
    }
}
